<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Console;

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Ahc\Phint\Util\Composer;
use Ahc\Phint\Util\Git;
use Ahc\Phint\Util\Path;

abstract class BaseCommand extends Command
{
    public function __construct()
    {
        $this->_git      = new Git;
        $this->_pathUtil = new Path;
        $this->_composer = new Composer;

        $this->defaults();
        $this->onConstruct();
    }

    protected function onConstruct()
    {
        // ;)
    }

    protected function promptAll(Interactor $io, array $promptConfig)
    {
        $template = ['default' => null, 'choices' => [], 'retry' => 1, 'extra' => '', 'restore' => false];

        foreach ($this->missingOptions($promptConfig) as $name => $option) {
            $config  = ($promptConfig[$name] ?? []) + $template;
            $default = $config['default'] ?? $option->default();

            if ($config['choices']) {
                $value = $io->choice($option->desc(), $config['choices'], $default);
            } else {
                $value = $io->prompt($option->desc() . $config['extra'], $default, null, $config['retry']);
            }

            if ($config['restore']) {
                $value = $config['choices'][$value] ?? $value;
            }

            $this->set($name, $value);
        }
    }

    protected function missingOptions(array $config)
    {
        return \array_filter($this->userOptions(), function ($name) use ($config) {
            return null === $this->$name && false !== ($config[$name] ?? null);
        }, \ARRAY_FILTER_USE_KEY);
    }

    protected function getCachePath(): string
    {
        if (!\Phar::running(false)) {
            return __DIR__ . '/../../.cache';
        }

        return $this->_pathUtil->getPhintPath('.cache');
    }
}
