<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Console;

use Ahc\Phint\Generator\TwigGenerator;
use Ahc\Phint\Util\Composer;
use CrazyFactory\DocBlocks\DocBlock;

class DocsCommand extends BaseCommand
{
    /** @var string Command name */
    protected $_name = 'docs';

    /** @var string Command description */
    protected $_desc = 'Generate basic readme docs from docblocks';

    /** @var string Current working dir */
    protected $_workDir;

    /**
     * Configure the command options/arguments.
     *
     * @return void
     */
    protected function onConstruct()
    {
        $this->_workDir  = \realpath(\getcwd());

        $this
            ->option('-o --output', "Output file (default README.md)\nFor old project you should use something else")
            ->option('-a --with-abstract', 'Create stub for abstract/interface class')
            ->usage(
                '<bold>  phint docs</end>               Appends to readme.md<eol/>' .
                '<bold>  phint d</end> <comment>-o docs/api.md</end>   Writes to docs/api.md<eol/>'
            );
    }

    /**
     * Generate test stubs.
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

        $io->comment('Preparing metadata ...', true);
        $metadata = $this->prepare();

        if (empty($metadata)) {
            $io->bgGreen('Looks like nothing to do here', true);

            return;
        }

        $io->comment('Generating tests ...', true);
        $generated = $this->generate($metadata);

        if ($generated) {
            $io->cyan("$generated test(s) generated", true);
        }

        $io->ok('Done', true);
    }

    protected function prepare(): array
    {
        // Sorry psr-0!
        $namespaces = $this->_composer->config('autoload.psr-4');

        $srcPaths = [];
        foreach ($namespaces as $ns => $path) {
            if (\preg_match('!^(source|src|lib|class)/?!', $path)) {
                $srcPaths[] = $this->_pathUtil->join($this->_workDir, $path);
            } else {
                unset($namespaces[$ns]);
            }
        }

        $classes = $this->_pathUtil->loadClasses($srcPaths, \array_keys($namespaces));

        return $this->getClassesMetadata($classes);
    }

    protected function getClassesMetadata(array $classes): array
    {
        $metadata = [];

        foreach ($classes as $classFqcn) {
            if ([] === $meta = $this->getClassMetadata($classFqcn)) {
                continue;
            }

            $metadata[] = $meta;
        }

        return $metadata;
    }

    protected function getClassMetadata(string $classFqcn): array
    {
        $reflex = new \ReflectionClass($classFqcn);

        if (!$this->shouldGenerateDocs($reflex)) {
            return [];
        }

        $methods     = [];
        $isTrait     = $reflex->isTrait();
        $isAbstract  = $reflex->isAbstract();
        $isInterface = $reflex->isInterface();

        foreach ($reflex->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->class !== $classFqcn) {
                continue;
            }

            $methods[$m->name] = $this->getMethodMetadata($m);
        }

        if (empty($methods)) {
            return [];
        }

        $texts = (new DocBlock($reflex))->texts();
        $title = \array_shift($texts);

        return \compact('classFqcn', 'isTrait', 'title', 'texts', 'methods');
    }

    protected function shouldGenerateDocs(\ReflectionClass $reflex): bool
    {
        if ($this->abstract) {
            return true;
        }

        return !$reflex->isInterface() && !$reflex->isAbstract();
    }

    protected function getMethodMetadata(\ReflectionMethod $method): array
    {
        $params = [];
        $parser = new DocBlock($method);

        foreach ($parser->find('param') as $param) {
            $params[] = \preg_replace(['/(.*\$\w+)(.*)/', '/ +/'], ['$1', ' '], $param->getValue());
        }

        if (null !== $return = $parser->first('return')) {
            $return = $return->getValue();
        }

        $texts = $parser->texts();
        $title = \array_shift($texts);

        return ['static' => $method->isStatic()] + \compact('title', 'texts', 'params', 'return');
    }

    protected function generate(array $metadata): int
    {
        $templatePath = __DIR__ . '/../../resources';
        $generator    = new TwigGenerator($templatePath, $this->getCachePath());

        return $generator->generateDocs($metadata, $this->values());
    }
}
