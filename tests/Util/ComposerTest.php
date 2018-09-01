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

use Ahc\Phint\Util\Composer;
use PHPUnit\Framework\TestCase;

class ComposerTest extends TestCase
{
    public function testCreateProjectOnWithoutComposerBinary()
    {
        $composer = new Composer;
        $composer->withWorkDir(sys_get_temp_dir());
        $result = $composer->createProject('project-name', 'app-name');

        $this->assertInstanceOf(Composer::class, $result);
        $this->assertFalse($result->successful());
    }

    public function testInstallOnWithoutComposerBinary()
    {
        $composer = new Composer;
        $composer->withWorkDir(sys_get_temp_dir());
        $result = $composer->install();

        $this->assertInstanceOf(Composer::class, $result);
        $this->assertFalse($result->successful());
    }

    public function testUpdateOnWithoutComposerBinary()
    {
        $composer = new Composer;
        $composer->withWorkDir(sys_get_temp_dir());
        $result = $composer->update();

        $this->assertInstanceOf(Composer::class, $result);
        $this->assertFalse($result->successful());
    }

    public function testDumpAutoloadOnWithoutComposerBinary()
    {
        $composer = new Composer;
        $composer->withWorkDir(sys_get_temp_dir());
        $result = $composer->dumpAutoload();

        $this->assertInstanceOf(Composer::class, $result);
        $this->assertFalse($result->successful());
    }

    public function testConfigOnNullDefaultValue()
    {
        $composer = new Composer;
        $composer->withWorkDir(sys_get_temp_dir());

        $this->assertNull($composer->config('name'));
    }

    public function testConfigOnDefaultValue()
    {
        copy(__DIR__ . '/../../composer.json', sys_get_temp_dir() . '/composer.json');
        $composer = new Composer;
        $composer->withWorkDir(sys_get_temp_dir());

        $this->assertSame('app/name', $composer->config('name.license', 'app/name'));
    }
}
