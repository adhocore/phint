<?php

namespace Ahc\Phint\Util;

class Composer extends Executable
{
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
}
