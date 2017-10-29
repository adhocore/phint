<?php

namespace Ahc\Phint\Util;

use Symfony\Component\Process\ExecutableFinder;

class Git
{
    /** @var array */
    protected $gitConfig;

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
        $finder    = new ExecutableFinder();

        exec(sprintf('"%s" config --list', $finder->find('git') ?: 'git'), $stdOut);

        foreach ($stdOut ?: [] as $config) {
            $parts = array_map('trim', explode('=', $config, 2)) + ['', ''];

            $gitConfig[$parts[0]] = $parts[1];
        }

        $this->gitConfig = $gitConfig;
    }
}
