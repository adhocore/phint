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

class DocsCommand extends BaseCommand
{
    /** @var string Command name */
    protected $_name = 'docs';

    /** @var string Command description */
    protected $_desc = 'Generate basic readme docs from docblocks';

    /**
     * Configure the command options/arguments.
     *
     * @return void
     */
    protected function onConstruct()
    {
        $this
            ->option(
                '-o --output',
                'Output file (default README.md). For old project you should use something else'
                . "\n(OR mark region with <!-- DOCS START --> and <!-- DOCS END --> to inject docs)",
                null,
                'README.md'
            )
            ->option('-a --with-abstract', 'Create docs for abstract/interface class')
            ->option('-x --template', "User supplied template path\nIt has higher precedence than inbuilt templates")
            ->usage(
                '<bold>  phint docs</end>               Appends to readme.md<eol/>' .
                '<bold>  phint d</end> <comment>-o docs/api.md</end>   Writes to docs/api.md<eol/>'
            );
    }

    /**
     * Generate test stubs.
     *
     * @return void
     */
    public function execute()
    {
        $io = $this->app()->io();

        $io->comment('Preparing metadata ...', true);
        $docsMetadata = $this->getClassesMetadata();

        if (empty($docsMetadata)) {
            $io->bgGreen('Looks like nothing to do here', true);

            return;
        }

        $io->comment('Generating docs ...', true);
        $generated = $this->generate($docsMetadata, $this->values());

        if ($generated) {
            $io->cyan("$generated doc(s) generated", true);
        }

        $io->ok('Done', true);
    }

    protected function generate(array $docsMetadata, array $parameters): int
    {
        $generator = new TwigGenerator($this->getTemplatePaths($parameters), $this->getCachePath());

        $parameters['output'] = $this->_pathUtil->expand($parameters['output'], $this->_workDir);

        return $generator->generateDocs($docsMetadata, $parameters);
    }
}
