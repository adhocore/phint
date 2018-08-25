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

class TwigGenerator implements GeneratorInterface
{
    /** @var \Twig_Environment */
    protected $twig;

    /** @var Path */
    protected $pathUtil;

    /** @var Inflector */
    protected $inflector;

    /** @var array */
    protected $templatePaths;

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

    public function __construct(array $templatePaths, string $cachePath)
    {
        $this->templatePaths = $templatePaths;
        $this->cachePath     = $cachePath;
        $this->pathUtil      = new Path;
        $this->inflector     = new Inflector;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(string $targetPath, array $parameters, CollisionHandlerInterface $handler = null): int
    {
        $count = 0;

        if (!$this->twig) {
            $this->initTwig();
        }

        $processed = [];
        $templates = $this->pathUtil->findFiles($this->templatePaths, '.twig', false);

        foreach ($templates as $template) {
            $relativePath = $this->pathUtil->getRelativePath($template, ...$this->templatePaths);

            if ($processed[$relativePath] ?? null) {
                continue;
            }

            $processed[$relativePath] = true;

            if ($this->shouldGenerate($template, $parameters)) {
                $count += (int) $this->doGenerate($relativePath, $targetPath, $parameters, $handler);
            }
        }

        $this->pathUtil->createBinaries($parameters['bin'] ?? [], $parameters['path'] ?? '');

        return $count;
    }

    public function generateTests(array $testMetadata, array $parameters): int
    {
        if (!$this->twig) {
            $this->initTwig();
        }

        $count = 0;

        foreach ($testMetadata as $metadata) {
            // Skip existing
            if (\is_file($targetFile = $metadata['testPath'])) {
                continue;
            }

            $content = $this->twig->render('tests/test.twig', $metadata + $parameters);
            $count  += (int) $this->pathUtil->writeFile($targetFile, $content);
        }

        return $count;
    }

    public function generateDocs(array $docsMetadata, array $parameters): int
    {
        if (!$this->twig) {
            $this->initTwig();
        }

        $targetFile = $parameters['output'] ?? 'README.md';
        $docContent = $this->twig->render('docs/docs.twig', \compact('docsMetadata') + $parameters);
        $docContent = "<!-- DOCS START -->\n$docContent\n<!-- DOCS END -->";

        if (null === $oldContent = \trim($this->pathUtil->read($targetFile))) {
            return (int) $this->pathUtil->writeFile($targetFile, $docContent);
        }

        if (\strpos($oldContent, '<!-- DOCS START -->') !== false) {
            $docContent = \preg_replace('~<!-- DOCS START -->.*?<!-- DOCS END -->~s', $docContent, $oldContent);

            return (int) $this->pathUtil->writeFile($targetFile, $docContent);
        }

        return (int) $this->pathUtil->writeFile($targetFile, \trim("$oldContent\n\n$docContent"));
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
            new \Twig_Loader_Filesystem($this->templatePaths),
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

    protected function doGenerate(string $relativePath, string $targetPath, array $parameters, CollisionHandlerInterface $handler = null): bool
    {
        $targetFile   = $this->pathUtil->join($targetPath, $this->getRelativeTarget($parameters, $relativePath));
        $fileExists   = \is_file($targetFile);
        $content      = $this->twig->render($relativePath, $parameters);

        if ($handler && $fileExists) {
            return $handler->handle($targetFile, $content, $parameters);
        }

        if ($this->mayOverride($fileExists, $parameters)) {
            return $this->pathUtil->writeFile($targetFile, $content);
        }

        return false;
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

        if (empty($parameters['travis'])) {
            return $name !== '.travis.yml';
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
