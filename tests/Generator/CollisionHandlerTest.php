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

use Ahc\Phint\Generator\CollisionHandler;
use PHPUnit\Framework\TestCase;

class CollisionHandlerTest extends TestCase
{
    public function testConstructor()
    {
        $this->assertInstanceOf(CollisionHandler::class, new CollisionHandler());
    }

    public function testHandle()
    {
        $targetjsonFile   = __DIR__ . '/../fixtures/example.json';
        $collisionHandler = new CollisionHandler();
        $collisionHandler->handle($targetjsonFile, \json_encode(['key' => 'value']));

        $content = \json_decode(\file_get_contents($targetjsonFile), true);

        $this->assertArrayHasKey('type', $content);
        $this->assertArrayHasKey('key', $content);
    }
}
