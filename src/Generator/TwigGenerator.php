<?php

namespace Ahc\Phint\Generator;

use Ahc\Phint\Util\Path;
use Symfony\Component\Finder\Finder;

class TwigGenerator implements GeneratorInterface
{
    /** @var \Twig_Environment */
    protected $twig;

    /** @var Path */
    protected $pathUtil;

    /** @var string|array */
    protected $templatePath;

    /** @var string */
    protected $cachePath;

    /** @var array Templates required for type 'project' only */
    protected $projectTemplates = [
        '.env.example' => true,
        'package.json' => true,
    ];

    public function __construct($templatePath, $cachePath)
    {
        $this->templatePath = $templatePath;
        $this->cachePath    = $cachePath;
        $this->pathUtil     = new Path;
    }

    /**
     * {@inheritdoc}
     */
    public function generate($targetPath, array $parameters, CollisionHandlerInterface $handler = null)
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
    }

    protected function initTwig()
    {
        $this->pathUtil->ensureDir($this->cachePath);

        $options = [
            'auto_reload' => true,
            'cache'       => $this->cachePath,
        ];

        $this->twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem($this->templatePath),
            $options
        );
    }

    protected function findTemplates($templatePath)
    {
        $finder    = new Finder;
        $templates = [];

        $finder->files()->ignoreDotFiles(false)->filter(function ($file) {
            return \substr($file, -5) === '.twig';
        });

        foreach ($finder->in($templatePath) as $file) {
            $templates[] = (string) $file;
        }

        return $templates;
    }

    protected function doGenerate($template, $targetPath, array $parameters, CollisionHandlerInterface $handler = null)
    {
        $relativePath = $this->pathUtil->getRelativePath($template, $this->templatePath);
        $targetFile   = $targetPath . '/' . str_replace('.twig', '', $relativePath);
        $targetDir    = \dirname($targetFile);
        $content      = $this->twig->render($relativePath, $parameters);

        if (\is_file($targetFile) && $handler) {
            $handler->handle($targetFile, $content, $parameters);

            return;
        }

        $this->pathUtil->ensureDir($targetDir);

        $this->pathUtil->writeFile($targetFile, $content);
    }

    protected function shouldGenerate($template, array $parameters)
    {
        $name = basename($template, '.twig');

        if (isset($this->projectTemplates[$name])) {
            return $parameters['type'] === 'project';
        }

        return true;
    }
}
