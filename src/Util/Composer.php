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

use Ahc\Cli\Exception\RuntimeException;

class Composer extends Executable
{
    /** @var array Content of composer.json decoded */
    protected $config = null;

    /** @var string The binary executable */
    protected $binary = 'composer';

    public function createProject($project, $using)
    {
        $this->runCommand(sprintf('create-project %s %s', $using, $project));

        return $this;
    }

    public function install()
    {
        $this->runCommand('install --prefer-dist --optimize-autoloader --no-suggest');

        return $this;
    }

    public function update()
    {
        $this->runCommand('update --prefer-dist --optimize-autoloader --no-suggest');

        return $this;
    }

    public function dumpAutoload()
    {
        $this->runCommand('dump-autoload --optimize');

        return $this;
    }

    public function config(string $key, $default = null)
    {
        $this->initConfig();

        $temp = $this->config;
        foreach (\explode('.', $key) as $part) {
            if (\is_array($temp) && \array_key_exists($part, $temp)) {
                $temp = $temp[$part];
            } else {
                return $default;
            }
        }

        return $temp;
    }

    protected function initConfig()
    {
        if (null === $this->config) {
            $this->config = (new Path)->readAsJson($this->workDir . '/composer.json');
        }

        if (null === $this->config) {
            throw new RuntimeException("Non existent or invalid composer.json at {$this->workDir}");
        }
    }
}
