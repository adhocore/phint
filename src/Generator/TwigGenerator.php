<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Generator;

use Ahc\Phint\Util\Inflector;
use Ahc\Phint\Util\Path;
use Symfony\Component\Finder\Finder;

class TwigGenerator implements GeneratorInterface
{
    /** @var \Twig_Environment */
    protected $twig;

    /** @var Path */
    protected $pathUtil;

    /** @var Inflector */
    protected $inflector;

    /** @var string|array */
    protected $templatePath;

    /** @var string */
    protected $cachePath;

    /** @var array Templates required for type 'project' only */
    protected $projectTemplates = [
        '.env.example' => true,
        'package.json' => true,
    ];

    /** @var array Templates only loaded by some specific commands */
    protected $commandTemplates = [
        'test' => true,
        'docs' => true,
    ];

    public function __construct(string $templatePath, string $cachePath)
    {
        $this->templatePath = $templatePath;
        $this->cachePath    = $cachePath;
        $this->pathUtil     = new Path;
        $this->inflector    = new Inflector;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $targetPath, array $parameters, CollisionHandlerInterface $handler = null)
    {
        if (!$this->twig) {
            $this->initTwig();
        }

        $templates = $this->findTemplates($this->templatePath);
        foreach ($templates as $template) {
            if ($this->shouldGenerate($template, $parameters)) {
                $this->doGenerate($template, $targetPath, $parameters, $handler);
            }
        }

        $this->pathUtil->createBinaries($parameters['bin'] ?? [], $parameters['path'] ?? '');
    }

    public function generateTests(array $testMetadata, array $parameters): int
    {
        if (!$this->twig) {
            $this->initTwig();
        }

        $generated = 0;

        foreach ($testMetadata as $metadata) {
            // Skip existing
            if (\is_file($targetFile = $metadata['testPath'])) {
                continue;
            }

            $generated++;

            $content = $this->twig->render('tests/test.twig', $metadata + $parameters);

            $this->pathUtil->writeFile($targetFile, $content);
        }

        return $generated;
    }

    protected function initTwig()
    {
        $options = [
            'auto_reload' => true,
            'cache'       => false,
        ];

        if ($this->cachePath) {
            $this->pathUtil->ensureDir($this->cachePath);
            $options['cache'] = $this->cachePath;
        }

        $this->twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem($this->templatePath),
            $options
        );

        $this->twig->addFilter(new \Twig_SimpleFilter('snake', function ($x) {
            return $this->inflector->snakeCase($x);
        }));

        $this->twig->addFilter(new \Twig_SimpleFilter('lcfirst', function ($x) {
            return \lcfirst($x);
        }));

        $this->twig->addFilter(new \Twig_SimpleFilter('ucfirst', function ($x) {
            return \ucfirst($x);
        }));

        $this->twig->addFunction(new \Twig_Function('gmdate', function ($f = null) {
            return \gmdate($f ?? 'Y-m-d H:i:s');
        }));

        $this->twig->addFilter(new \Twig_SimpleFilter('call', function ($fn) {
            return $fn(\array_slice(\func_get_args(), 1));
        }));
    }

    protected function findTemplates(string $templatePath)
    {
        $templates = [];
        $finder    = new Finder;

        $finder->files()->ignoreDotFiles(false)->filter(function ($file) {
            return \substr($file, -5) === '.twig';
        });

        foreach ($finder->in($templatePath) as $file) {
            $templates[] = (string) $file;
        }

        return $templates;
    }

    protected function doGenerate(string $template, string $targetPath, array $parameters, CollisionHandlerInterface $handler = null)
    {
        $relativePath = $this->pathUtil->getRelativePath($template, $this->templatePath);
        $targetFile   = $this->pathUtil->join($targetPath, $this->getRelativeTarget($parameters, $relativePath));
        $fileExists   = \is_file($targetFile);
        $targetDir    = \dirname($targetFile);
        $content      = $this->twig->render($relativePath, $parameters);

        if ($handler && $fileExists) {
            $handler->handle($targetFile, $content, $parameters);

            return;
        }

        if ($this->mayOverride($fileExists, $parameters)) {
            $this->pathUtil->writeFile($targetFile, $content);
        }
    }

    protected function getRelativeTarget(array $parameters, string $relativePath): string
    {
        $fileName   = \basename($relativePath, '.twig');
        $targetFile = \str_replace('.twig', '', $relativePath);

        if (!empty($parameters['ghTemplate']) && \in_array($fileName, ['ISSUE_TEMPLATE.md', 'PULL_REQUEST_TEMPLATE.md'])) {
            $targetFile = '.github/' . $fileName;
        }

        return $targetFile;
    }

    protected function shouldGenerate(string $template, array $parameters)
    {
        $name = \basename($template, '.twig');

        if (isset($this->projectTemplates[$name])) {
            return $parameters['type'] === 'project';
        }

        if (isset($this->commandTemplates[$name])) {
            return false;
        }

        return true;
    }

    protected function mayOverride(bool $fileExists, array $parameters)
    {
        if (!$fileExists) {
            return true;
        }

        // If using reference package then we dont overwrite!
        if (!empty($parameters['using'])) {
            return false;
        }

        if (!empty($parameters['sync'])) {
            return false;
        }

        return true;
    }
}
