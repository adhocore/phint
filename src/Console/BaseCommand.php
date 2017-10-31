<?php

namespace Ahc\Phint\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

class BaseCommand extends Command
{
    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    protected function prompt($prompt, $default = null, $validator = null)
    {
        $prompt = '<info>' . $prompt . '</info>';

        if ($default !== null) {
            $prompt .= ' [<comment>' . $default . '</comment>]';
        }

        $helper   = $this->getHelper('question');
        $question = new Question($prompt . ': ', $default);

        $values = [];
        if (is_array($validator)) {
            $values    = $validator;
            $validator = null;
        }

        $question->setValidator($validator ?: function ($value) use ($values) {
            if (empty($value)) {
                throw new \InvalidArgumentException('Please provide non empty value');
            }

            if (!empty($values) && !in_array($value, $values)) {
                throw new \InvalidArgumentException('Value should be one of: ' . implode(', ', $values));
            }

            return $value;
        });

        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }
}
