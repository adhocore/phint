<?php

namespace Ahc\Phint\Console;

use Ahc\Cli\Input\Command;
use Ahc\Cli\IO\Interactor;
use Ahc\Phint\Generator\CollisionHandler;
use Ahc\Phint\Generator\TwigGenerator;
use Ahc\Phint\Util\Composer;
use Ahc\Phint\Util\Git;
use Ahc\Phint\Util\Inflector;
use Ahc\Phint\Util\Path;

/**
 * Some ideas related to phar taken from `composer selfupdate`.
 */
class UpdateCommand extends Command
{
    const PHAR_URL = 'https://github.com/adhocore/phint/releases/download/{version}/phint.phar';

    public function __construct()
    {
        parent::__construct('update', 'Update Phint to lastest version');

        $this
            ->option('-r --rollback', 'Rollback to earlier version', 'boolval', false)->on([$this, 'rollback'])
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
        $io->bold('Fetching latest version ...', true);

        $release = \shell_exec('curl https://api.github.com/repos/adhocore/phint/releases/latest');
        $release = \json_decode($release);

        if (\JSON_ERROR_NONE !== \json_last_error() || empty($release->assets[0])) {
            $io->bgRed('Error getting latest release', true);

            return;
        }

        $latest = $release->tag_name;

        if (!\version_compare($this->version, $latest, '<')) {
            $io->bgGreen('You seem to have latest version already', true);
        } else {
            $this->updateTo($latest, $release->assets[0]->size);

            if (\is_file($this->getPharPathFor(null) . '.old')) {
                $io->colors('You can run <comment>phint selfupdate --rollback</end> to revert<eol/>');
            }
        }
    }

    /**
     * Perform rollback.
     */
    public function rollback()
    {
        $io = $this->app()->io();

        $io->bold('Rolling back to earlier version ...', true);

        $thisPhar = $this->getPharPathFor(null);
        $oldPhar  = $thisPhar . '.old';

        if (!\is_file($oldPhar)) {
            throw new \RuntimeException('No old version locally available');
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

        // Create new $version phar
        $saved = @\file_put_contents($versionPhar, \shell_exec("curl -sSL $sourceUrl"));

        if ($saved < $size) {
            @\unlink($versionPhar);

            throw new \RuntimeException("Couldnt download the phar for $version");
        }

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
        $thisPhint = $_SERVER['argv'][0];
        $thisPhint = '/home/adhocore/phint.phar';

        if ($version === null) {
            return $thisPhint;
        }

        $pathTemplate = "%s.%s.phar";

        return \sprintf($pathTemplate, \str_replace('.phar', '', $thisPhint), $version);
    }
}
