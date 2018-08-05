<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https//:github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Test;

use Ahc\Phint\Generator\TwigGenerator;
use PHPUnit\Framework\TestCase;

class TwigGeneratorTest extends TestCase
{
    protected $templatePath = __DIR__ . '/../fixtures/twig';

    public function setUp()
    {
        @\mkdir(__DIR__ . '/../fixtures/twig');
    }

    public function testGenerate()
    {
        $twigGenerator = new TwigGenerator(__DIR__ . '/../fixtures/twig', __DIR__ . '/../fixtures/twig_cache');

        $this->assertNull($twigGenerator->generate(__DIR__ . '/../fixtures/twig', []));
    }
}
