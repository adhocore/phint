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
            ->addOption('description', 'd', InputOption::VALUE_OPTIONAL, 'Project description')
            ->addOption('name', 'm', InputOption::VALUE_OPTIONAL, 'Vendor full name, defaults to git name')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Vendor handle/username')
            ->addOption('email', 'e', InputOption::VALUE_OPTIONAL, 'Vendor email, defaults to git email')
            ->addOption('namespace', 's', InputOption::VALUE_OPTIONAL, 'Root namespace')
            ->addOption('year', 'y', InputOption::VALUE_OPTIONAL, 'License Year', date('Y'))
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Project type')
            ->addOption('using', 'z', InputOption::VALUE_OPTIONAL, 'Packagist name of reference project (eg: laravel/lumen)')
            ->addOption('keywords', 'l', InputOption::VALUE_OPTIONAL, 'Project Keywords')
            ->addOption('php', 'p', InputOption::VALUE_OPTIONAL, 'Minimum PHP version project needs')
            ->setHelp(<<<'EOT'
The <info>init</info> command creates a new project with all basic files and
structures in the <project-name> directory. See some examples below:

<info>phint init</info> project-name <comment>--force --description "My awesome project" --name "Your Name" --email "you@domain.com"</comment>
<info>phint init</info> project-name <comment>--using laravel/lumen --namespace Project/Api --type project</comment>

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
        $output->writeln('<info>Preparing ...</info>');

        $composer   = new Composer;
        $parameters = $this->input->getOptions() + $this->input->getArguments();

        if (null !== $using = $this->input->getOption('using')) {
            $this->output->writeln('Using <comment>' . $using . '</comment> to create project');

            $composer->withOutput($this->output)->createProject($projectPath, $using);
        }

        $this->output->writeln('<comment>Generating files ...</comment>');

        $this->generate($parameters['path'], $parameters);

        $this->output->writeln('Setting up <info>git</info>');

        $this->git->withWorkDir($parameters['path'])->init()
            ->addRemote($parameters['username'], $parameters['project']);

        $this->output->writeln('Setting up <info>composer</info>');

        $composer->withWorkDir($parameters['path'])->install();

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

        $this->input->setOption('path', $this->prepareProjectPath());

        $this->git = new Git;
        $inflector = new Inflector;

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

        $this->input->setOption('php', $this->input->getOption('php') ?: $this->prompt(
            'Minimum PHP version project needs',
            PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            ['5.4', '5.5', '5.6', '7.0', '7.1']
        ));
    }

    protected function generate($projectPath, array $parameters)
    {
        $templatePath = __DIR__ . '/../../resources';
        $cachePath    = __DIR__ . '/../../.cache';

        $generator = new TwigGenerator($templatePath, $cachePath);

        $generator->generate($projectPath, $parameters, new CollisionHandler);
    }
}
