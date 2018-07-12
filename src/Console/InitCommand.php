<?php

namespace Ahc\Phint\Console;

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Ahc\Phint\Generator\CollisionHandler;
use Ahc\Phint\Generator\TwigGenerator;
use Ahc\Phint\Util\Composer;
use Ahc\Phint\Util\Git;
use Ahc\Phint\Util\Inflector;
use Ahc\Phint\Util\Path;

class InitCommand extends Command
{
    /** @var Git */
    protected $_git;

    /** @var Composer */
    protected $_composer;

    /**
     * Configure the command options/arguments.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('init', 'Create and Scaffold a bare new PHP project');

        $this->_git      = new Git;
        $this->_composer = new Composer;
        $this->_action   = [$this, 'execute'];

        $this
            ->argument('<project>', 'The project name without slashes')
            ->option('-t --type', 'Project type', null, 'library')
            ->option('-n --name', 'Vendor full name', null, $this->_git->getConfig('user.name'))
            ->option('-e --email', 'Vendor email', null, $this->_git->getConfig('user.email'))
            ->option('-u --username', 'Vendor handle/username')
            ->option('-N --namespace', 'Root namespace')
            ->option('-k --keywords [words...]', 'Project Keywords')
            ->option('-P --php', 'Minimum PHP version', null, PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION)
            ->option('-p --path', 'The project path (Auto resolved)')
            ->option('-f --force', 'Run even if the project exists')
            ->option('-d --descr', 'Project description')
            ->option('-y --year', 'License Year', null, date('Y'))
            ->option('-z --using', 'Reference package name')
            ->option('-c --config', 'JSON filepath to read config from')
            ->option('-r --req [pkgs...]', 'Required packages')
            ->option('-D --dev [pkgs...]', 'Developer packages')
            ->usage(<<<'EOT'
The <info>init</info> command creates a new project with all basic files and
structures in the <project-name> directory. See some examples below:

<info>phint init</info> project-name <comment>--force --description "My awesome project" --name "Your Name" --email "you@domain.com"</comment>
<info>phint init</info> project-name <comment>--using laravel/lumen --namespace Project/Api --type project</comment>
<info>phint init</info> project-name <comment>--php 5.6 --config /path/to/json --dev mockery/mockery --req doctrine/dbal --req symfony/console</comment>
EOT
            );
    }

    /**
     * Execute the command action.
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

        if ($this->using) {
            $io->write('Using ') . $io->comment($this->using) . $io->write(' to create project', true);

            $this->_composer->createProject($this->path, $this->using);
        }

        $io->comment('Generating files ...', true);
        $this->generate($this->path, $this->values());

        $io->write('Setting up ')->cyanBold('git', true);
        $this->_git->withWorkDir($this->path)->init()->addRemote($this->username, $this->project);

        $io->write('Setting up ')->cyanBold('composer')->comment(' (takes some time)', true);
        $this->_composer->withWorkDir($this->path)->install();

        $io->ok('Done', true);
    }

    protected function prepareProjectPath()
    {
        $path = $this->project;
        $io   = $this->app()->io();

        if (!(new Path)->isAbsolute($path)) {
            $path = \getcwd() . '/' . $path;
        }

        if (\is_dir($path)) {
            if (!$this->force) {
                throw new \InvalidArgumentException('Something with the same name already exists!');
            }

            if (!$this->using) {
                $io->error('You have set force flag, existing files will be overwritten', true);
            }
        } else {
            \mkdir(\rtrim($path, '/') . '/src', 0777, true);
        }

        return $path;
    }

    public function interact(Interactor $io)
    {
        $project = $this->project;

        if (!\preg_match('/[a-z0-9_-]/i', $project)) {
            throw new \InvalidArgumentException('Project argument should only contain [a-z0-9_-]');
        }

        $io->okBold('Phint Setup', true);

        $this->set('path', $path = $this->prepareProjectPath());
        $this->loadConfig($this->config);

        $setup = [
            'type' => ['choices' => ['project', 'library', 'composer-plugin']],
            'php'  => ['choices' => ['5.4', '5.5', '5.6', '7.0', '7.1', '7.2']],
            'using'  => ['prompt' => 0],
        ];

        $options = $this->userOptions();
        foreach ($options as $name => $option) {
            $default = $option->default();
            if ($this->$name !== null || \in_array($name, ['req', 'dev', 'config'])) {
                continue;
            }

            $set = $setup[$name] ?? [];
            if ($set['choices'] ?? null) {
                $value = $io->choice($option->desc(), $set['choices'], $default);
            } else {
                $value = $io->prompt($option->desc(), $default, null, $set['prompt'] ?? 1);
            }

            if ($name === 'namespace' && \stripos($value, $project) === false) {
                 $value .= '\\' . ucfirst($project);
            }
            if ($name === 'keywords') {
                $value = \array_map('trim', \explode(',', $value));
            }

            $this->set($name, $value);
        }

        $this->collectPackages();
    }

    protected function generate($projectPath, array $parameters)
    {
        $templatePath = __DIR__ . '/../../resources';
        $cachePath    = __DIR__ . '/../../.cache';

        $generator = new TwigGenerator($templatePath, $cachePath);

        $generator->generate($projectPath, $parameters, new CollisionHandler);
    }

    protected function loadConfig($path = null)
    {
        if (empty($path)) {
            return;
        }

        $pathUtil = new Path;

        if (!$pathUtil->isAbsolute($path)) {
            $path = \getcwd() . '/' . $path;
        }

        if (!\is_file($path)) {
            $this->app()->io()->error('Invalid path specified for config');

            return;
        }

        $config = $pathUtil->readAsJson($path);
        unset($config['path']);

        foreach ($config as $key => $value) {
            $this->set($key, $value);
        }
    }

    protected function collectPackages()
    {
        $fn = function ($pkg) {
            if (!empty($pkg) && strpos($pkg, '/') === false) {
                throw new \InvalidArgumentException(
                    'Package name format should be vendor/package:version (version can be omitted)'
                );
            }

            return $pkg;
        };

        $io = $this->app()->io();

        foreach (['req' => 'Required', 'dev' => 'Developer'] as $key => $label) {
            $pkgs = $this->$key;

            if (!$pkgs) {
                do {
                    $pkgs[] = $io->prompt($label . ' package (press ENTER to skip)', null, $fn, 0);

                    if (!end($pkgs)) {
                        array_pop($pkgs);

                        break;
                    }
                } while (true);
            }

            foreach ($pkgs as &$pkg) {
                if (strpos($pkg, ':') === false) {
                    $pkg .= ':@stable';
                }

                $pkg = array_combine(['name', 'version'], explode(':', $pkg, 2));
            }

            $this->set($key, $pkgs);
        }
    }
}
