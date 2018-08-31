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

use Ahc\Cli\IO\Interactor;
use Ahc\Phint\Generator\TwigGenerator;
use Ahc\Phint\Util\Composer;

class TestCommand extends BaseCommand
{
    /** @var string Command name */
    protected $_name = 'test';

    /** @var string Command description */
    protected $_desc = 'Generate test stubs';

    /**
     * Configure the command options/arguments.
     *
     * @return void
     */
    protected function onConstruct()
    {
        $this
            ->option('-t --no-teardown', 'Dont add teardown method')
            ->option('-s --no-setup', 'Dont add setup method')
            ->option('-n --naming', "Test method naming format\n(t: testMethod | m: test_method | i: it_tests_)")
            ->option('-a --with-abstract', 'Create stub for abstract/interface class')
            ->option('-p --phpunit [classFqcn]', 'Base PHPUnit class to extend from')
            ->option('-x --template', "User supplied template path\nIt has higher precedence than inbuilt templates")
            ->usage(
                '<bold>  phint test</end> <comment>-n i</end>        With `it_` naming<eol/>' .
                '<bold>  phint t</end> <comment>--no-teardown</end>  Without `tearDown()`<eol/>' .
                '<bold>  phint test</end> <comment>-a</end>          With stubs for abstract/interface<eol/>'
            );
    }

    public function interact(Interactor $io)
    {
        $promptConfig = [
            'naming' => [
                'choices' => ['t' => 'testMethod', 'i' => 'it_tests_', 'm' => 'test_method'],
                'default' => 't',
            ],
            'phpunit' => [
                'default' => 'PHPUnit\\Framework\\TestCase',
            ],
            'template' => false,
        ];

        $this->logging('start');
        $this->promptAll($io, $promptConfig);
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
        $generated = $this->generate($metadata, $this->values());

        if ($generated) {
            $io->cyan("$generated test(s) generated", true);
        }

        $io->ok('Done', true);
        $this->logging('end');
    }

    protected function prepare(): array
    {
        // Sorry psr-0!
        $namespaces  = $this->_composer->config('autoload.psr-4');
        $namespaces += $this->_composer->config('autoload-dev.psr-4');

        $srcNs = $testNs = [];

        foreach ($namespaces as $ns => $path) {
            $ns = \rtrim($ns, '\\') . '\\';
            if (\preg_match('!^(source|src|lib|class)/?!', $path)) {
                $path    = \rtrim($path, '/\\') . '/';
                $srcNs[] = ['ns' => $ns, 'nsPath' => "{$this->_workDir}/$path"];
            } elseif (\strpos($path, 'test') === 0) {
                $path   = \rtrim($path, '/\\') . '/';
                $testNs = ['ns' => $ns, 'nsPath' => "{$this->_workDir}/$path"];
            }
        }

        if (empty($srcNs) || empty($testNs)) {
            throw new \RuntimeException(
                'The composer.json#(autoload.psr-4, autoload-dev.psr-4) contains no `src` or `test` paths'
            );
        }

        return $this->getTestMetadata($this->getSourceClasses(), $srcNs, $testNs);
    }

    protected function getTestMetadata(array $classes, array $srcNs, array $testNs): array
    {
        $metadata = [];

        foreach ($classes as $classFqcn) {
            if ([] === $meta = $this->getClassMetaData($classFqcn)) {
                continue;
            }

            $metadata[] = $meta + $this->convertToTest($meta, $srcNs, $testNs);
        }

        return $metadata;
    }

    private function convertToTest(array $metadata, array $srcNs, array $testNs): array
    {
        $srcNspcs  = \array_column($srcNs, 'ns');
        $testClass = $metadata['className'] . 'Test';
        $testPath  = \str_replace(\array_column($srcNs, 'nsPath'), $testNs['nsPath'], $metadata['classPath']);
        $testPath  = \preg_replace('!\.php$!i', 'Test.php', $testPath);
        $testFqcn  = \str_replace($srcNspcs, $testNs['ns'], $metadata['classFqcn']) . 'Test';

        $testNamespace = \trim(\str_replace($srcNspcs, $testNs['ns'], $metadata['namespace'] . '\\'), '\\');

        return compact('testClass', 'testNamespace', 'testFqcn', 'testPath');
    }

    protected function generate(array $testMetadata, array $parameters): int
    {
        $generator = new TwigGenerator($this->getTemplatePaths($parameters), $this->getCachePath());

        return $generator->generateTests($testMetadata, $parameters);
    }
}
