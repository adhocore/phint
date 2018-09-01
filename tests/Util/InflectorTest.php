<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

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

    public function testSnakeCase()
    {
        $inflector = new Inflector;

        $this->assertEquals('this_will_be_snake_case_string', $inflector->snakeCase('this will be snake case string'));
    }

    public function testWords()
    {
        $inflector = new Inflector;

        $this->assertEquals('This Will Be Ucword String', $inflector->words('this will be ucword string'));
    }
}
