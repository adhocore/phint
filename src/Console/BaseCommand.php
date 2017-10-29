<?php

namespace Ahc\Phint\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

class BaseCommand extends Command
{
    /** @var InputInterface */
    protected $input;

    /** @var InputInterface */
    protected $output;

    protected function prompt($prompt, $default = null, callable $validator = null)
    {
        $helper   = $this->getHelper('question');
        $question = new Question($prompt, $default);

        $question->setValidator($validator ?: function ($value) {
            if (empty($value)) {
                throw new \InvalidArgumentException('Please provide non empty value');
            }

            return $value;
        });

        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }
}
