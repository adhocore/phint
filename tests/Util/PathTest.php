<?php

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

    public function testWriteFile()
    {
        $writeFilePath = __DIR__ . '/../fixtures/write_file.txt';
        $path          = new Path;
        $path->writeFile($writeFilePath, 'write_file_test');

        $this->assertEquals('write_file_test', \file_get_contents($writeFilePath));
    }
}
