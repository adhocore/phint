<?php

namespace Ahc\Phint\Test;

use Ahc\Phint\Util\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorTest extends TestCase
{
    public function testStuldyCase()
    {
        $inflector = new Inflector;

        $this->assertEquals('ThisWillBeUcwordString', $inflector->stuldyCase('this-will-be-ucword-string'));
    }

    public function testWords()
    {
        $inflector = new Inflector;

        $this->assertEquals('This Will Be Ucword String', $inflector->words('this will be ucword string'));
    }
}
