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

use Ahc\Phint\Generator\CollisionHandler;
use PHPUnit\Framework\TestCase;

class CollisionHandlerTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(CollisionHandler::class, new CollisionHandler());
    }

    public function testHandleOnJsonFile()
    {
        $targetjsonFile   = __DIR__ . '/../fixtures/example.json';
        $collisionHandler = new CollisionHandler();
        $collisionHandler->handle($targetjsonFile, \json_encode(['key' => 'value']));

        $content = \json_decode(\file_get_contents($targetjsonFile), true);

        $this->assertArrayHasKey('type', $content);
        $this->assertArrayHasKey('key', $content);
    }

    public function testHandleOnMarkdownFile()
    {
        $targetjsonFile     = __DIR__ . '/../fixtures/example.md';
        $expectedjsonFile   = __DIR__ . '/../fixtures/expected_example.md';
        $collisionHandler   = new CollisionHandler();
        $collisionHandler->handle($targetjsonFile, "key\nvalue\n");

        $content         = \file_get_contents($targetjsonFile);
        $expectedContent = \file_get_contents($expectedjsonFile);

        $this->assertEquals($expectedContent, $content);
    }

    public function testHandleOnUnsupportedFile()
    {
        $targetjsonFile   = __DIR__ . '/../fixtures/example.txt';
        $collisionHandler = new CollisionHandler();

        $this->assertFalse($collisionHandler->handle($targetjsonFile, "key\nvalue\n"));
    }
}
