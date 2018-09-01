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

use Ahc\Phint\Generator\TwigGenerator;
use PHPUnit\Framework\TestCase;

class TwigGeneratorTest extends TestCase
{
    protected $templatePath;

    public function setUp()
    {
        $path = __DIR__ . '/../fixtures/twig';

        if (!is_dir($path . '_cache')) {
            mkdir($path . '_cache', 0777);
        }

        $this->templatePath = realpath($path);
    }

    public function testGenerate()
    {
        $twigGenerator = new TwigGenerator([$this->templatePath], __DIR__ . '/../fixtures/twig_cache');

        $rand = '_' . rand();
        $this->assertSame(1, $twigGenerator->generate($this->templatePath, ['string' => $rand]));

        $this->assertContains($rand, file_get_contents($this->templatePath . '/example'));
    }

    public function testGenerateTestsOnExistedTestTwigFile()
    {
        $twigGenerator = new TwigGenerator([$this->templatePath], __DIR__ . '/../fixtures/twig_cache');
        $metaData      = [
            ['testPath' => __DIR__ . '/../fixtures/twig/test.twig'],
        ];
        $parameters = [
            'string' => 'here',
        ];

        $this->assertSame(0, $twigGenerator->generateTests($metaData, $parameters));
    }

    public function testGenerateTestsOnNonExistedTestTwigFile()
    {
        $twigGenerator = new TwigGenerator([$this->templatePath], __DIR__ . '/../fixtures/twig_cache');
        $metaData      = [
            ['testPath' => __DIR__ . '/../fixtures/test_non_exsited.twig'],
        ];
        $parameters = [
            'string' => 'here',
        ];

        $this->assertSame(1, $twigGenerator->generateTests($metaData, $parameters));
    }

    public function testGeneradteDocs()
    {
        $twigGenerator = new TwigGenerator([$this->templatePath], __DIR__ . '/../fixtures/twig_cache');
        $metaData      = [];
        $parameters    = [
            'output' => __DIR__ . '/../fixtures/doc.md',
        ];

        $this->assertSame(1, $twigGenerator->generateDocs($metaData, $parameters));
    }
}
