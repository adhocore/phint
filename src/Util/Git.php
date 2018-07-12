<?php

namespace Ahc\Phint\Util;

class Git extends Executable
{
    /** @var array */
    protected $gitConfig;

    /** @var string The binary executable */
    protected $binary = 'git';

    /**
     * Gets git config.
     *
     * @param string|null $key
     *
     * @return mixed
     */
    public function getConfig($key = null)
    {
        if (null === $this->gitConfig) {
            $this->loadConfig();
        }

        if (null === $key) {
            return $this->gitConfig;
        }

        return isset($this->gitConfig[$key]) ? $this->gitConfig[$key] : null;
    }

    protected function loadConfig()
    {
        $gitConfig = [];

        $output = $this->runCommand('config --list');
        $output = explode("\n", str_replace(["\r\n", "\r"], "\n", $output));

        foreach ($output as $config) {
            $parts = array_map('trim', explode('=', $config, 2)) + ['', ''];

            $gitConfig[$parts[0]] = $parts[1];
        }

        $this->gitConfig = $gitConfig;
    }

    public function init()
    {
        $this->runCommand('init');

        return $this;
    }

    public function addRemote($username, $project)
    {
        $this->runCommand(sprintf('remote add origin git@github.com:%s/%s.git', $username, $project));

        return $this;
    }
}
