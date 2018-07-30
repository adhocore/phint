<?php

namespace Ahc\Phint\Console;

use Ahc\Cli\Exception\InvalidArgumentException;
use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Ahc\Phint\Generator\CollisionHandler;
use Ahc\Phint\Generator\TwigGenerator;
use Ahc\Phint\Util\Composer;
use Ahc\Phint\Util\Git;
use Ahc\Phint\Util\Inflector;
use Ahc\Phint\Util\Path;

class InitCommand extends BaseCommand
{
    /** @var string Command name */
    protected $_name = 'init';

    /** @var string Command description */
    protected $_desc = 'Create and Scaffold a bare new PHP project';

    /**
     * Configure the command options/arguments.
     *
     * @return void
     */
    protected function onConstruct()
    {
        $this
            ->argument('<project>', 'The project name without slashes')
            ->option('-T --type', 'Project type')
            ->option('-n --name', 'Vendor full name', null, $this->_git->getConfig('user.name'))
            ->option('-e --email', 'Vendor email', null, $this->_git->getConfig('user.email'))
            ->option('-u --username', 'Vendor handle/username')
            ->option('-N --namespace', 'Root namespace (use `/` separator)')
            ->option('-w --keywords [words...]', 'Project Keywords (`php`, `<project>` auto added)')
            ->option('-P --php', 'Minimum PHP version', 'floatval')
            ->option('-p --path', 'The project path (Auto resolved)')
            ->option('-f --force', 'Run even if the project exists', null, false)
            ->option('-d --descr', 'Project description')
            ->option('-y --year', 'License Year', null, date('Y'))
            ->option('-z --using', 'Reference package')
            ->option('-C --config', 'JSON filepath to read config from')
            ->option('-R --req [pkgs...]', 'Required packages')
            ->option('-D --dev [pkgs...]', 'Developer packages')
            ->option('-t --no-travis', 'Disable travis')
            ->option('-c --no-codecov', 'Disable codecov')
            ->option('-s --no-scrutinizer', 'Disable scrutinizer')
            ->option('-l --no-styleci', 'Disable StyleCI')
            ->option('-L --license', 'License')
            ->usage($this->writer()->colorizer()->colors(''
                . '<bold>  phint init</end> <line><project></end> '
                . '<comment>--force --descr "Awesome project" --name "YourName" --email you@domain.com</end><eol/>'
                . '<bold>  phint init</end> <line><project></end> '
                . '<comment>--using laravel/lumen --namespace Project/Api --type project</end><eol/>'
                . '<bold>  phint init</end> <line><project></end> '
                . '<comment>--php 7.0 --config /path/to/json --dev mockery/mockery --req adhocore/cli</end><eol/>'
            ));
    }

    /**
     * Execute the command action.
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

        if ($using = $this->using) {
            $io->colors("Using <cyanBold>$using</end> to create project <comment>(takes some time)</end><eol/>");

            $this->_composer->createProject($this->path, $this->using);
        }

        $io->comment('Generating files ...', true);
        $this->generate($this->path, $this->values());

        $io->colors('Setting up <cyanBold>git</end><eol/>');
        $this->_git->withWorkDir($this->path)->init()->addRemote($this->username, $this->project);

        $io->colors('Setting up <cyanBold>composer</end> <comment>(takes some time)</end><eol>');
        if ($using) {
            $this->_composer->withWorkDir($this->path)->update();
        } else {
            $this->_composer->withWorkDir($this->path)->install();
        }

        $success = $this->_composer->successful();

        $success ? $io->ok('Done', true) : $io->error('Composer setup failed', true);
    }

    public function interact(Interactor $io)
    {
        $project = $this->project;

        if (!\preg_match('/[a-z0-9_-]/i', $project)) {
            throw new InvalidArgumentException('Project argument should only contain [a-z0-9_-]');
        }

        $io->okBold('Phint Setup', true);

        $this->set('path', $path = $this->prepareProjectPath());
        $this->loadConfig($this->config);

        $this->collectMissing($io);
        $this->collectPackages($io);
    }

    protected function prepareProjectPath(): string
    {
        $path = $this->project;
        $io   = $this->app()->io();

        if (!$this->_pathUtil->isAbsolute($path)) {
            $path = \getcwd() . '/' . $path;
        }

        if (\is_dir($path)) {
            if (!$this->force) {
                throw new InvalidArgumentException(
                    \sprintf('Something with the name "%s" already exists!', \basename($path))
                );
            }

            if (!$this->using) {
                $io->error('You have set force flag, existing files will be overwritten', true);
            }
        } else {
            \mkdir($path, 0777, true);
        }

        return $path;
    }

    protected function loadConfig(string $path = null)
    {
        if (empty($path)) {
            return;
        }

        if (!$this->_pathUtil->isAbsolute($path)) {
            $path = \getcwd() . '/' . $path;
        }

        if (!\is_file($path)) {
            $this->app()->io()->error('Invalid path specified for config', true);

            return;
        }

        foreach ($this->_pathUtil->readAsJson($path) as $key => $value) {
            $this->$key ?? $this->set($key, $value);
        }
    }

    protected function collectMissing(Interactor $io)
    {
        $promptConfig = [
            'type' => [
                'choices' => ['p' => 'project', 'l' => 'library', 'c' => 'composer-plugin'],
                'default' => 'l',
                'restore' => true,
            ],
            'license' => [
                'choices' => ['m' => 'MIT', 'g' => 'GNULGPL', 'a' => 'Apache2', 'b' => 'BSDSimple'],
                'default' => 'm',
            ],
            'php' => [
                'choices' => ['5.4', '5.5', '5.6', '7.0', '7.1', '7.2'],
                'default' => '7.0',
            ],
            'using'    => ['retry' => 0, 'extra' => ' (ENTER to skip)'],
            'keywords' => ['retry' => 0, 'extra' => ' (ENTER to skip)'],

            // Donot promt these here!
            'req'    => false,
            'dev'    => false,
            'config' => false,
        ];

        $this->promptAll($io, $promptConfig);
    }

    protected function collectPackages(Interactor $io)
    {
        foreach (['req' => 'Required', 'dev' => 'Developer'] as $key => $label) {
            $pkgs = $this->$key ?: $this->promptPackages($label, $io);

            foreach ($pkgs as &$pkg) {
                $pkg = \strpos($pkg, ':') === false ? "{$pkg}:@stable" : $pkg;
                $pkg = \array_combine(['name', 'version'], \explode(':', $pkg, 2));
            }

            $this->set($key, $pkgs);
        }
    }

    protected function promptPackages(string $label, Interactor $io): array
    {
        $pkgs = [];

        do {
            if (!$pkg = $io->prompt($label . ' package (ENTER to skip)', null, [$this, 'validatePackage'], 0)) {
                break;
            }

            $pkgs[] = $pkg;
        } while (true);

        return $pkgs;
    }

    public function validatePackage(string $pkg): string
    {
        $pkg = \trim($pkg);

        if ($pkg && \strpos($pkg, '/') === false) {
            throw new InvalidArgumentException(
                'Package name format should be vendor/package:version (version can be omitted)'
            );
        }

        return $pkg;
    }

    protected function generate(string $projectPath, array $parameters)
    {
        $templatePath = __DIR__ . '/../../resources';
        $generator    = new TwigGenerator($templatePath, $this->getCachePath());

        // Normalize license (default MIT)
        $parameters['license']   = \strtolower($parameters['license'][0] ?? 'm');
        $parameters['namespace'] = $this->makeNamespace($parameters['namespace']);
        $parameters['keywords']  = $this->makeKeywords($parameters['keywords']);

        $generator->generate($projectPath, $parameters, new CollisionHandler);
    }

    protected function makeNamespace(string $value): string
    {
        $in = new Inflector;

        $project = $this->project;
        $value   = $in->stuldyCase(\str_replace([' ', '/'], '\\', $value));
        $project = $in->stuldyCase(\str_replace([' ', '/', '\\'], '-', $project));

        if (\stripos($value, $project) === false) {
            $value .= '\\' . $project;
        }

        return $value;
    }

    protected function makeKeywords($value): array
    {
        $default = ['php', $this->project];

        if (empty($value)) {
            return $default;
        }

        if (\is_string($value)) {
            $value = \array_map('trim', \explode(',', $value));
        }

        return \array_values(\array_unique(\array_merge($default, $value)));
    }
}
