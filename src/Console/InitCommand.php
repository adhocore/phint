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
            ->setDescription('Create a bare new PHP project')
            ->addArgument('project', InputArgument::REQUIRED, 'The project name')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force initialization even if the project exists')
            ->addOption('description', 'd', InputArgument::OPTIONAL, 'Project description')
            ->addOption('name', 'm', InputArgument::OPTIONAL, 'Vendor full name, defaults to git name')
            ->addOption('username', 'u', InputArgument::OPTIONAL, 'Vendor handle or username',
                getenv('GITHUB_USERNAME')
            )
            ->addOption('email', 'e', InputArgument::OPTIONAL, 'Vendor email, defaults to git email')
            ->addOption('namespace', 's', InputArgument::OPTIONAL, 'Root namespace')
            ->addOption('year', 'y', InputArgument::OPTIONAL, 'License Year', date('Y'))
            ->addOption('type', 't', InputArgument::OPTIONAL, 'Project type', 'library')
            ->addOption('using', 'z', InputArgument::OPTIONAL, 'Packagist name of reference project (eg: laravel/lumen)');
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
        $this->input  = $input;
        $this->output = $output;

        $output->writeln('<info>Preparing ...</info>');

        $projectPath = $this->prepareProjectPath();
        $this->git   = new Git($projectPath);
        $parameters  = $this->collectParameters();
        $composer    = new Composer;

        if (null !== $using = $this->input->getOption('using')) {
            $this->output->writeln('Using <comment>' . $using . '</comment> to create project');

            $composer->withOutput($this->output)->createProject($projectPath, $using);
        }

        $this->output->writeln('<comment>Generating files ...</comment>');

        $this->generate($projectPath, $parameters);

        $this->output->writeln('Setting up <info>git</info>');

        $this->git->init()->addRemote($parameters['username'], $parameters['project']);

        $output->writeln('<comment>Done</comment>');

        $this->output->writeln('Setting up <info>composer</info>');

        $composer->withWorkDir($projectPath)->install();
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
            \mkdir($path, 0777, true);
        }

        return $path;
    }

    protected function collectParameters()
    {
        $inflector   = new Inflector;
        $project     = $this->input->getArgument('project');
        $Project     = $inflector->words($project);
        $year        = $this->input->getOption('year');
        $type        = $this->input->getOption('type');
        $vendorName  = $this->input->getOption('name') ?: $this->git->getConfig('user.name');
        $vendorEmail = $this->input->getOption('email') ?: $this->git->getConfig('user.email');

        $description = $this->input->getOption('description') ?: $this->prompt(
            'Project description [<comment>A brief intro about this project</comment>]: '
        );

        $username = $this->input->getOption('username') ?: $this->prompt(
            'Vendor Handle [<comment>Often your github username, set GITHUB_USERNAME env variable to automate</comment>]: '
        );

        $namespace = $inflector->stuldyCase($username) . '/' . $inflector->stuldyCase($project);
        $namespace = $this->input->getOption('namespace') ?: $this->prompt(
            'Root Namespace [<comment>Defaults to ' . $namespace . '</comment>]: ',
            $namespace
        );

        $namespace = \str_replace('/', '\\\\', $namespace);
        $keywords  = ['php', $project];

        return \compact(
            'year', 'project', 'vendorName', 'vendorEmail', 'description',
            'username', 'namespace', 'keywords', 'Project', 'type'
        );
    }

    protected function generate($projectPath, array $parameters)
    {
        $templatePath = __DIR__ . '/../../resources';
        $cachePath    = __DIR__ . '/../../.cache';

        $generator = new TwigGenerator($templatePath, $cachePath);

        $generator->generate($projectPath, $parameters, new CollisionHandler);
    }
}
