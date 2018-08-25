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

use Ahc\Phint\Generator\TwigGenerator;

class ExportCommand extends BaseCommand
{
    /** @var string Command name */
    protected $_name = 'export';

    /** @var string Command description */
    protected $_desc = 'Export factory templates so you can customize and use them';

    /**
     * Configure the command options/arguments.
     *
     * @return void
     */
    protected function onConstruct()
    {
        $this
            ->option('-t --to <directory>', 'Output directory')
            ->option('-o --overwrite', 'Overwrite if target file exists', 'boolval', false)
            ->usage(
                '<bold>  phint export</end> -t .        Exports to current dir<eol/>' .
                '<bold>  phint e</end> <comment>--to ~/myphint</end>   Exports to ~/myphint dir<eol/>'
            );
    }

    /**
     * Generate test stubs.
     *
     * @return void
     */
    public function execute()
    {
        $io  = $this->app()->io();
        $res = \realpath(__DIR__ . '/../../resources');
        $dir = $this->_pathUtil->expand($this->to, $this->_workDir);

        $this->_pathUtil->ensureDir($dir);

        $count     = 0;
        $templates = $this->_pathUtil->findFiles([$res], '.twig', true);

        $io->comment('Exporting ...', true);

        foreach ($templates as $template) {
            $target = \str_replace($res, $dir, $template);

            if (\is_file($target) && !$this->overwrite) {
                continue;
            }

            $content = \file_get_contents($template);
            $count  += (int) $this->_pathUtil->writeFile($target, $content);
        }

        $io->cyan("$count template(s) copied to {$this->to}", true);
        if ($count) {
            $io->comment('Now you can customize those templates and use like so:', true);
            $io->bold('  phint init --template ' . $this->_pathUtil->expand($this->to), true);
        }

        $io->ok('Done', true);
    }
}
