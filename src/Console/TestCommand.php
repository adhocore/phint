<?php

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
            ->option('-t --no-teardown', 'Dont add teardown method')
            ->option('-s --no-setup', 'Dont add setup method')
            ->option('-n --naming', 'Test method naming format')
            ->option('-a --with-abstract', 'Create stub for abstract/interface class')
            ->option('-p --phpunit [classFqcn]', 'Base PHPUnit class to extend from')
            ->option('-d --dump-autoload', 'Force composer dumpautoload (slow)', null, false)
            ->usage(
                '<bold>  phint test</end> <comment>-n i</end>        With `it_` naming<eol/>' .
                '<bold>  phint t</end> <comment>--no-teardown</end>  Without `tearDown()`<eol/>' .
                '<bold>  phint test</end> <comment>-a</end>          With stubs for abstract method<eol/>'
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
                'default' => \class_exists('\\PHPUnit\\Framework\\TestCase')
                    ? 'PHPUnit\\Framework\\TestCase'
                    : 'PHPUnit_Framework_TestCase',
            ],
        ];

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

        // Generate namespace mappings
        if ($this->dumpAutoload) {
            $io->colors('Running <cyanBold>composer dumpautoload</end> <comment>(takes some time)</end><eol>');
            $this->_composer->dumpAutoload();
        }

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
        $namespaces  = $this->_composer->config('autoload.psr-4');
        $namespaces += $this->_composer->config('autoload-dev.psr-4');

        $testNs = [];
        foreach ($namespaces as $ns => $path) {
            if (!\preg_match('!src/?|lib/?!', $path)) {
                unset($namespaces[$ns]);
            }

            if (\strpos($path, 'test') === 0) {
                $path   = \rtrim($path, '/\\');
                $nsPath = "{$this->_workDir}/$path";
                $testNs = \compact('ns', 'nsPath');
            } elseif ([] === $testNs) {
                $ns     = $ns . '\\Test';
                $nsPath = "{$this->_workDir}/tests";
                $testNs = \compact('ns', 'nsPath');
            }
        }

        $classMap = require $this->_workDir . '/vendor/composer/autoload_classmap.php';

        return $this->getTestMetadata($classMap, $namespaces, $testNs);
    }

    protected function getTestMetadata(array $classMap, array $namespaces, array $testNs): array
    {
        $testMeta = [];

        require_once $this->_workDir . '/vendor/autoload.php';

        foreach ($classMap as $classFqcn => $classPath) {
            foreach ($namespaces as $ns => $nsPath) {
                if (\strpos($classFqcn, $ns) !== 0) {
                    continue;
                }

                if ([] === $meta = $this->getClassMetadata($classFqcn)) {
                    continue;
                }

                $data       = \compact('classFqcn', 'classPath', 'ns', 'nsPath');
                $testMeta[] = $meta + $this->convertToTest($data, $testNs);
            }
        }

        return $testMeta;
    }

    protected function getClassMetadata(string $classFqcn)
    {
        $reflex = new \ReflectionClass($classFqcn);

        if (!$this->isAllowed($reflex)) {
            return [];
        }

        $methods     = [];
        $isTrait     = $reflex->isTrait();
        $newable     = $reflex->isInstantiable();
        $isAbstract  = $reflex->isAbstract();
        $isInterface = $reflex->isInterface();
        $excludes    = ['__construct', '__destruct'];

        foreach ($reflex->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->class !== $classFqcn || \in_array($m->name, $excludes)) {
                continue;
            }

            $methods[\ltrim($m->name, '_')] = ['static' => $m->isStatic(), 'abstract' => $m->isAbstract()];
        }

        return \compact('classFqcn', 'isTrait', 'isAbstract', 'isInterface', 'newable', 'methods');
    }

    protected function isAllowed(\ReflectionClass $class)
    {
        if ($this->abstract) {
            return true;
        }

        return !$reflex->isInterface() && !$reflex->isAbstract();
    }

    private function convertToTest(array $metadata, array $testNs): array
    {
        $classFqcn  = $metadata['classFqcn'];
        $classPath  = \realpath($metadata['classPath']);
        $nsFullPath = $this->_workDir . '/' . \trim($metadata['nsPath'], '/\\') . '/';
        $testPath   = \preg_replace('!^' . \preg_quote($nsFullPath) . '!', $testNs['nsPath'] . '/', $classPath);
        $testPath   = \preg_replace('!\.php$!i', 'Test.php', $testPath);
        $testFqcn   = \preg_replace('!^' . \preg_quote($metadata['ns']) . '!', $testNs['ns'], $classFqcn);
        $fqcnParts  = \explode('\\', $testFqcn);
        $className  = \array_pop($fqcnParts);
        $testFqns   = \implode('\\', $fqcnParts);
        $testFqcn   = $testFqcn . '\\Test';

        return compact('className', 'testFqns', 'testFqcn', 'testPath');
    }

    protected function generate(array $testMetadata): int
    {
        $templatePath = __DIR__ . '/../../resources';
        $generator    = new TwigGenerator($templatePath, $this->getCachePath());

        return $generator->generateTests($testMetadata, $this->values());
    }
}
