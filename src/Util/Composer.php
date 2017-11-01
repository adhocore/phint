<?php

namespace Ahc\Phint\Util;

class Composer extends Executable
{
    public function __construct($workDir = null, $binary = null)
    {
        parent::__construct($workDir, $binary ?: 'composer');
    }

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
}
