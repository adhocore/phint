<?php

namespace Ahc\Phint\Console;

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Ahc\Phint\Generator\TwigGenerator;
use Ahc\Phint\Util\Composer;

class TestCommand extends Command
{
    /** @var Composer */
    protected $_composer;

    /** @var string Current working dir */
    protected $_workDir;

    public function __construct()
    {
        parent::__construct('test', 'Generate test stubs');

        $this->_workDir  = \realpath(\getcwd());
        $this->_composer = (new Composer)->withWorkDir($this->_workDir);

        $this
            ->option('-t --no-teardown', 'Dont add teardown method')
            ->option('-s --no-setup', 'Dont add setup method')
            ->option('-n --naming', 'Test method naming format')
            ->option('-p --phpunit [classFqcn]', 'Base PHPUnit class to extend from')
            ->option('-d --dump-autoload', 'Base PHPUnit class to extend from', null, false);
    }

    public function interact(Interactor $io)
    {
        $setup = [
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

        foreach ($this->userOptions() as $name => $option) {
            if ($this->$name !== null) {
                continue;
            }

            $default = $setup[$name]['default'];
            if ($setup[$name]['choices'] ?? null) {
                $value = $io->choice($option->desc(), $setup[$name]['choices'], $default);
            } else {
                $value = $io->prompt($option->desc(), $default);
            }

            $this->set($name, $value);
        }
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

                $testMeta[] = $this->getClassMetadata($classFqcn)
                    + $this->convertToTest(\compact('classFqcn', 'classPath', 'ns', 'nsPath'), $testNs);
            }
        }

        return $testMeta;
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

    protected function getClassMetadata(string $classFqcn)
    {
        $methods = [];
        $reflex  = new \ReflectionClass($classFqcn);
        $newable = $reflex->isInstantiable();

        foreach ($reflex->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->isAbstract() || $m->isDestructor()) {
                continue;
            }

            $methods[$m->name] = ['static' => $m->isStatic()];
        }

        return \compact('classFqcn', 'newable', 'methods');
    }

    protected function generate(array $testMetadata): int
    {
        $templatePath = __DIR__ . '/../../resources';
        $generator    = new TwigGenerator($templatePath, '');

        return $generator->generateTests($testMetadata, $this->values());
    }
}
