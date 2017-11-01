<?php

namespace Ahc\Phint\Console;

use Ahc\Phint\Generator\CollisionHandler;
use Ahc\Phint\Generator\TwigGenerator;
use Ahc\Phint\Util\Composer;
use Ahc\Phint\Util\Git;
use Ahc\Phint\Util\Inflector;
use Ahc\Phint\Util\Path;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends BaseCommand
{
    /** @var Git */
    protected $git;

    /** @var Composer */
    protected $composer;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Scaffold a bare new PHP project')
            ->addArgument('project', InputArgument::REQUIRED, 'The project name without slashes')
            ->addOption('path', null, InputOption::VALUE_NONE, 'The project path (Auto resolved)')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Run even if the project exists')
            ->addOption('description', 'i', InputOption::VALUE_OPTIONAL, 'Project description')
            ->addOption('name', 'm', InputOption::VALUE_OPTIONAL, 'Vendor full name, defaults to git name')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Vendor handle/username')
            ->addOption('email', 'e', InputOption::VALUE_OPTIONAL, 'Vendor email, defaults to git email')
            ->addOption('namespace', 's', InputOption::VALUE_OPTIONAL, 'Root namespace')
            ->addOption('year', 'y', InputOption::VALUE_OPTIONAL, 'License Year', date('Y'))
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Project type')
            ->addOption('using', 'z', InputOption::VALUE_OPTIONAL, 'Reference package name (eg: laravel/lumen)')
            ->addOption('keywords', 'l', InputOption::VALUE_OPTIONAL, 'Project Keywords')
            ->addOption('php', 'p', InputOption::VALUE_OPTIONAL, 'Minimum PHP version project needs')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'JSON filepath to read config from')
            ->addOption('req', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Required packages', [])
            ->addOption('dev', 'd', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Developer packages', [])
            ->setHelp(<<<'EOT'
The <info>init</info> command creates a new project with all basic files and
structures in the <project-name> directory. See some examples below:

<info>phint init</info> project-name <comment>--force --description "My awesome project" --name "Your Name" --email "you@domain.com"</comment>
<info>phint init</info> project-name <comment>--using laravel/lumen --namespace Project/Api --type project</comment>
<info>phint init</info> project-name <comment>--php 5.6 --config /path/to/json --dev mockery/mockery --req doctrine/dbal --req symfony/console</comment>
EOT
            );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters = $this->input->getOptions() + $this->input->getArguments();

        if (null !== $using = $parameters['using']) {
            $this->output->writeln('Using <comment>' . $using . '</comment> to create project');

            $this->composer->createProject($parameters['path'], $using);
        }

        $this->output->writeln('<comment>Generating files ...</comment>');

        $this->generate($parameters['path'], $parameters);

        $this->output->writeln('Setting up <info>git</info>');

        $this->git->init()->addRemote($parameters['username'], $parameters['project']);

        $this->output->writeln('Setting up <info>composer</info>');

        $this->composer->install();

        $output->writeln('<comment>Done</comment>');
    }

    protected function prepareProjectPath()
    {
        $path = $this->input->getArgument('project');

        if (!(new Path)->isAbsolute($path)) {
            $path = \getcwd() . '/' . $path;
        }

        if (\file_exists($path)) {
            if (!$this->input->getOption('force')) {
                throw new \InvalidArgumentException('Something with the same name already exists!');
            }

            if (!$this->input->getOption('using')) {
                $this->output->writeln('<error>You have set force flag, existing files will be overwritten</error>');
            }
        } else {
            \mkdir(\rtrim($path, '/') . '/src', 0777, true);
        }

        return $path;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $project = $input->getArgument('project');

        if (empty($project) || !preg_match('/[a-z0-9_-]/i', $project)) {
            throw new \InvalidArgumentException('Project argument is required and should only contain [a-z0-9_-]');
        }

        $this->loadConfig($this->input->getOption('config'));

        $this->input->setOption('path', $path = $this->prepareProjectPath());

        $this->git      = (new Git)->withOutput($this->output)->withWorkDir($path);
        $this->composer = (new Composer)->withOutput($this->output)->withWorkDir($path);

        $this->output->writeln('<info>Phint Setup</info>');
        $this->output->writeln('<comment>Just press ENTER if you want to use the [default] or skip<comment>');
        $this->output->writeln('');

        $this->input->setOption('type', $this->input->getOption('type') ?: $this->prompt(
            'Project type (project/library)',
            'library',
            ['project', 'library', 'composer-plugin']
        ));

        $this->input->setOption('name', $this->input->getOption('name') ?: $this->prompt(
            'Vendor full name',
            $this->git->getConfig('user.name')
        ));

        $this->input->setOption('email', $this->input->getOption('email') ?: $this->prompt(
            'Vendor email',
             $this->git->getConfig('user.email')
        ));

        $this->input->setOption('description', $this->input->getOption('description') ?: $this->prompt(
            'Brief project description'
        ));

        $this->input->setOption('username', $username = $this->input->getOption('username') ?: $this->prompt(
            'Vendor handle (often github username)',
            getenv('VENDOR_USERNAME') ?: null
        ));

        $inflector = new Inflector;

        $namespace = $this->input->getOption('namespace') ?: $this->prompt(
            'Project root namespace (forward slashes are auto fixed)',
            (getenv('VENDOR_NAMESPACE') ?: $inflector->stuldyCase($username))
                . '/' . $inflector->stuldyCase($project)
        );

        $this->input->setOption('namespace', \str_replace('/', '\\\\', $namespace));

        $keywords = $this->input->getOption('keywords') ?: $this->prompt(
            'Project keywords (CSV)',
            "php, $project"
        );

        $this->input->setOption('keywords', array_map('trim', explode(',', $keywords)));

        $this->input->setOption('php', floatval($this->input->getOption('php') ?: $this->prompt(
            'Minimum PHP version project needs',
            PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            ['5.4', '5.5', '5.6', '7.0', '7.1']
        )));

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
            $path = getcwd() . '/' . $path;
        }

        if (!is_file($path)) {
            $this->output->writeln('<error>Invalid path specified for config</error>');

            return;
        }

        $config = (new Path)->readAsJson($path);

        if (empty($config)) {
            return;
        }

        unset($config['path']);

        foreach ($config as $key => $value) {
            if ($this->input->hasOption($key)) {
                $this->input->setOption($key, $value);
            }

            if ($key === 'vendor_namespace') {
                putenv('VENDOR_NAMESPACE=' . $value);
            }
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

        foreach (['req' => 'Required', 'dev' => 'Developer'] as $key => $label) {
            $pkgs = $this->input->getOption($key);

            if (!$pkgs) {
                do {
                    $pkgs[] = $this->prompt($label . ' package (press ENTER to skip)', null, $fn);

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

            $this->input->setOption($key, $pkgs);
        }
    }
}
