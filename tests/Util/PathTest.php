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

use Ahc\Phint\Util\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testIsAbsolute()
    {
        $path = new Path;

        $this->assertTrue($path->isAbsolute(__DIR__ . '/../fixtures/'));
    }

    public function relativePathProvider()
    {
        return [
            [__DIR__ . '/../fixtures', __DIR__ . '/../fixtures', ''],
            [__DIR__ . '/../fixtures', '/../fixtures', __DIR__ . '/../fixtures'],
        ];
    }

    /** @dataProvider relativePathProvider */
    public function testGetRelativePath($fullPath, $relativePath, $expectedPath)
    {
        $path = new Path;

        $this->assertEquals($expectedPath, $path->getRelativePath($fullPath, $relativePath));
    }

    public function testEnsureDir()
    {
        $path = new Path;

        $this->assertTrue($path->ensureDir(__DIR__ . '/../fixtures/ensure_dir'));
    }

    public function testEnsureDirOnNonExistedDir()
    {
        $path = new Path;

        $this->assertTrue($path->ensureDir(__DIR__ . '/../fixtures/add_dir'));
    }

    public function testGetExtension()
    {
        $path = new Path;

        $this->assertEquals('json', $path->getExtension(__DIR__ . '/../fixtures/example.json'));
    }

    public function testReadAsJson()
    {
        $path = new Path;

        $this->assertArrayHasKey('name', $path->readAsJson(__DIR__ . '/../fixtures/example.json'));
    }

    public function testReadDirShouldReturnNull()
    {
        $path = new Path;

        $this->assertNull($path->read(__DIR__ . '/../fixtures'));
    }

    public function testGetPhintPath()
    {
        $path = new Path;

        $this->assertContains('/home', $path->getPhintPath('/fixtures'));
    }

    public function testGetPhintPathOnEmptySubPath()
    {
        $path = new Path;

        $this->assertContains('', $path->getPhintPath());
    }

    public function testWriteFile()
    {
        $writeFilePath = __DIR__ . '/../fixtures/write_file.txt';
        $path          = new Path;
        $path->writeFile($writeFilePath, 'write_file_test');

        $this->assertEquals('write_file_test', \file_get_contents($writeFilePath));
    }

    public function testJoinOnEmptyPathArray()
    {
        $path = new Path;

        $this->assertSame('', $path->join([]));
    }

    public function testLoadClasses()
    {
        $path = new Path;

        $this->assertContains('Ahc\Phint\Test\PathTest', $path->loadClasses([__DIR__ . '/../../src'], ['Ahc\Phint\Test\PathTest']));
    }

    public function testExpandOnEmptyPath()
    {
        $path = new Path;

        $this->assertSame('', $path->expand('.'));
    }

    public function testExpandOnContainingHomePath()
    {
        $path = new Path;

        $this->assertContains('/home', $path->expand('~'));
    }

    public function testExpandOnRelativePath()
    {
        $path = new Path;

        $this->assertSame('../fixtures/.', $path->expand('./', '../fixtures'));
    }

    public function testExpandOnAbsolutePath()
    {
        $path         = new Path;
        $absolutePath = '/usr/local/bin';

        $this->assertSame('/usr/local/bin', $path->expand($absolutePath));
    }
}
