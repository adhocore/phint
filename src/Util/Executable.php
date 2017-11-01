<?php

namespace Ahc\Phint\Util;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

abstract class Executable
{
    /** @var OutputInterface */
    protected $output;

    /** @var string The binary executable */
    protected $binary;

    /** @var string */
    protected $workDir;

    public function __construct($workDir = null, $binary = null)
    {
        $this->workDir = $workDir;
        $this->binary  = $binary ? '"' . $binary . '"' : $binary;
    }

    public function withOutput(OutputInterface $output = null)
    {
        $this->output = $output;

        return $this;
    }

    public function withWorkDir($workDir = null)
    {
        $this->workDir = $workDir;

        return $this;
    }

    protected function findBinary($binary)
    {
        if (\is_executable($binary)) {
            return $binary;
        }

        $finder = new ExecutableFinder();

        return $finder->find($binary) ?: $binary;
    }

    /**
     * Runs the command using underlying binary.
     *
     * @param string $command
     *
     * @return string|null The output of command.
     */
    protected function runCommand($command)
    {
        $self = $this;
        $proc = new Process($this->binary . ' ' . $command, $this->workDir, null, null, null);

        $proc->setPty(true);

        $proc->run(!$this->output ? null : function ($type, $buffer) use ($self) {
            $self->output->write($buffer);
        });

        if ($proc->isSuccessful()) {
            return $proc->getOutput();
        }
    }
}
