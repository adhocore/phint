<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Util;

use Ahc\Cli\Helper\Shell;
use Ahc\Cli\IO\Interactor;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

abstract class Executable
{
    /** @var bool Last command successful? */
    protected $isSuccessful = true;

    /** @var Interactor */
    protected $io;

    /** @var string The binary executable */
    protected $binary;

    /** @var string */
    protected $workDir;

    /** @var string Full path of log file */
    protected $logFile;

    public function __construct($binary = null, string $logFile = '')
    {
        $this->workDir = \getcwd();
        $this->logFile = $logFile;
        $this->binary  = $this->findBinary($binary ?? $this->binary);
    }

    public function withWorkDir($workDir = null)
    {
        if (!\is_dir($workDir)) {
            throw new \InvalidArgumentException('Not a valid working dir: ' . $workDir);
        }

        $this->workDir = $workDir;

        return $this;
    }

    public function successful(): bool
    {
        return $this->isSuccessful;
    }

    protected function findBinary(string $binary): string
    {
        if (\is_executable($binary)) {
            return '"' . $binary . '"';
        }

        $isWin = \DIRECTORY_SEPARATOR === '\\';

        return $isWin ? $this->findWindowsBinary($binary) : '"' . $binary . '"';
    }

    protected function findWindowsBinary(string $binary): string
    {
        $paths = \explode(\PATH_SEPARATOR, \getenv('PATH') ?: \getenv('Path'));

        foreach ($paths as $path) {
            foreach (['.exe', '.bat', '.cmd'] as $ext) {
                if (\is_file($file = $path . '\\' . $binary . $ext)) {
                    return '"' . $file . '"';
                }
            }
        }

        return '"' . $binary . '"';
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
        $proc = new Shell($this->binary . ' ' . $command);

        $proc->setOptions($this->workDir)->execute();

        (new Path)->writeFile($this->logFile, $proc->getErrorOutput(), \FILE_APPEND);

        $this->isSuccessful = 0 === $proc->getExitCode();

        return $proc->getOutput();
    }
}
