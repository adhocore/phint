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
use CrazyFactory\DocBlocks\DocBlock;

class DocsCommand extends BaseCommand
{
    /** @var string Command name */
    protected $_name = 'docs';

    /** @var string Command description */
    protected $_desc = 'Generate basic readme docs from docblocks';

    /**
     * Configure the command options/arguments.
     *
     * @return void
     */
    protected function onConstruct()
    {
        $this
            ->option('-o --output', 'Output file (default README.md). For old project you should use something else'
                . "\n(OR mark region with <!-- DOCS START --> and <!-- DOCS END --> to inject docs)",
                null, 'README.md'
            )
            ->option('-a --with-abstract', 'Create docs for abstract/interface class')
            ->option('-x --template', "User supplied template path\nIt has higher precedence than inbuilt templates")
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
        $docsMetadata = $this->prepare();

        if (empty($docsMetadata)) {
            $io->bgGreen('Looks like nothing to do here', true);

            return;
        }

        $io->comment('Generating docs ...', true);
        $generated = $this->generate($docsMetadata, $this->values());

        if ($generated) {
            $io->cyan("$generated doc(s) generated", true);
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

        $methods = [];
        $isTrait = $reflex->isTrait();
        $name    = $reflex->getShortName();
        $exclude = ['__construct', '__destruct'];

        foreach ($reflex->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->class !== $classFqcn && \in_array($m->name, $exclude)) {
                continue;
            }

            $methods[$m->name] = $this->getMethodMetadata($m);
        }

        if (empty($methods)) {
            return [];
        }

        $texts = (new DocBlock($reflex))->texts();
        $title = \array_shift($texts);

        return \compact('classFqcn', 'name', 'isTrait', 'title', 'texts', 'methods');
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
            $return = \preg_replace('/ .*?$/', '', $return->getValue());
        }

        $texts = $parser->texts();
        $title = \array_shift($texts);

        return ['static' => $method->isStatic()] + \compact('title', 'texts', 'params', 'return');
    }

    protected function generate(array $docsMetadata, array $parameters): int
    {
        $generator = new TwigGenerator($this->getTemplatePaths($parameters), $this->getCachePath());

        $parameters['output'] = $this->_pathUtil->expand($parameters['output'], $this->_workDir);

        return $generator->generateDocs($docsMetadata, $parameters);
    }
}
