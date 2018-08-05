<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https//:github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Console;

use Ahc\Cli\Exception\RuntimeException;
use Ahc\Cli\Input\Command;
use Ahc\Phint\Util\Composer;
use Ahc\Phint\Util\Path;

/**
 * Some ideas related to phar taken from `composer selfupdate`.
 */
class UpdateCommand extends BaseCommand
{
    const PHAR_URL = 'https://github.com/adhocore/phint/releases/download/{version}/phint.phar';

    /** @var string Command name */
    protected $_name = 'update';

    /** @var string Command description */
    protected $_desc = 'Update Phint to lastest version';

    protected function onConstruct()
    {
        $this
            ->option('-r --rollback', 'Rollback to earlier version', 'boolval', false)
                ->on([$this, 'rollback'])
            ->usage(
                '<bold>  phint update</end>        Updates to latest version<eol/>' .
                '<bold>  phint u</end>             Also updates to latest version<eol/>' .
                '<bold>  phint update</end> <comment>-r</end>     Rolls back to prev version<eol/>' .
                '<bold>  phint u</end> <comment>--rollback</end>  Also rolls back to prev version<eol/>'
            );
    }

    /**
     * Execute the command action.
     */
    public function execute()
    {
        $io = $this->app()->io();

        $io->cyan("Current version {$this->_version}", true);
        $io->comment('Fetching latest version ...', true);

        $release = \shell_exec('curl -sSL https://api.github.com/repos/adhocore/phint/releases/latest');
        $release = \json_decode($release);

        if (\JSON_ERROR_NONE !== \json_last_error() || empty($release->assets[0])) {
            $io->bgRed('Error getting latest release', true);

            return;
        }

        $latest = $release->tag_name;

        if (!\version_compare(\str_replace('v', '', $this->_version), \str_replace('v', '', $latest), '<')) {
            $io->bgGreen('You seem to have latest version already', true);
        } else {
            $this->updateTo($latest, $release->assets[0]->size);

            if (\is_file($this->getPharPathFor(null) . '.old')) {
                $io->colors('You can run <comment>phint update --rollback</end> to revert<eol/>');
            }
        }
    }

    /**
     * Perform rollback.
     */
    public function rollback()
    {
        $io = $this->app()->io();

        $io->cyan("Current version {$this->_version}", true);
        $io->comment('Rolling back to earlier version ...', true);

        $thisPhar = $this->getPharPathFor(null);
        $oldPhar  = $thisPhar . '.old';

        if (!\is_file($oldPhar)) {
            throw new RuntimeException('No old version locally available');
        }

        $oldPerms = \fileperms($thisPhar);

        if (@\rename($oldPhar, $thisPhar)) {
            $io->ok('Done', true);
        }

        @\chmod($thisPhar, $oldPerms);

        $this->emit('_exit');
    }

    protected function updateTo(string $version, int $size)
    {
        $io = $this->app()->io();

        $currentPhar  = $this->getPharPathFor(null);
        $versionPhar  = $this->getPharPathFor($version);
        $sourceUrl    = \str_replace('{version}', $version, static::PHAR_URL);

        $io->comment("Downloading phar $version ...", true);

        // Create new $version phar
        $saved = @\file_put_contents($versionPhar, \shell_exec("curl -sSL $sourceUrl"));

        if ($saved < $size) {
            @\unlink($versionPhar);

            throw new RuntimeException("Couldnt download the phar for $version");
        }

        $io->comment("Updating to $version ...", true);

        try {
            @\chmod($versionPhar, \fileperms($currentPhar));

            if (!\ini_get('phar.readonly')) {
                $phar = new \Phar($versionPhar);
                unset($phar);
            }

            // Take backup of current
            @\copy($currentPhar, $currentPhar . '.old');

            // Replace current with new $version
            @\rename($versionPhar, $currentPhar);

            $io->ok('Done', true);
        } catch (\Throwable $e) {
            $io->error('Couldnt update to ' . $version, true);
        }
    }

    protected function getPharPathFor(string $version = null): string
    {
        if (false === $thisPhint = \realpath($_SERVER['argv'][0])) {
            $thisPhint = $this->_pathUtil->getPhintPath('phint.phar');
        }

        if (empty($thisPhint)) {
            throw new RuntimeException('Couldnt locate phint path, make sure you have HOME in the env vars');
        }

        if (empty($version)) {
            return $thisPhint;
        }

        $pathTemplate = '%s.%s.phar';

        return \sprintf($pathTemplate, \str_replace('.phar', '', $thisPhint), $version);
    }
}
