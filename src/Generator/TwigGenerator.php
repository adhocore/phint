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

    public function __construct($templatePath, $cachePath)
    {
        $this->templatePath = $templatePath;
        $this->cachePath    = $cachePath;
        $this->pathUtil     = new Path;
    }

    public function generate($targetPath, array $parameters)
    {
        if (!$this->twig) {
            $this->initTwig();
        }

        $templates = $this->findTemplates($this->templatePath);
        foreach ($templates as $template) {
            $this->doGenerate($template, $targetPath, $parameters);
        }
    }

    protected function initTwig()
    {
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0777, true);
        }

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
            return substr($file, -5) === '.twig';
        });

        foreach ($finder->in($templatePath) as $file) {
            $templates[] = (string) $file;
        }

        return $templates;
    }

    protected function doGenerate($template, $targetPath, array $parameters)
    {
        $relativePath = $this->pathUtil->getRelativePath($template, $this->templatePath);
        $targetFile   = $targetPath . '/' . str_replace('.twig', '', $relativePath);
        $targetDir    = dirname($targetFile);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $content = $this->twig->render($relativePath, $parameters);

        // What could be easier?
        file_put_contents($targetFile, $content);
    }
}
