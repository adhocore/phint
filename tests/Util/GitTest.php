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

use Ahc\Phint\Util\Git;
use PHPUnit\Framework\TestCase;

class GitTest extends TestCase
{
    public function testGetConfig()
    {
        $git  = new Git;
        $conf = $git->getConfig();

        $this->assertArrayHasKey('remote.origin.url', $conf);
        $this->assertContains('adhocore/phint.git', $conf['remote.origin.url']);
    }

    public function testGetConfigOnSpecificKey()
    {
        $git = new Git;

        $this->assertSame('false', $git->getConfig('core.bare'));
    }

    public function testInit()
    {
        $git = new Git();

        $this->assertInstanceOf(Git::class, $git->init());
        $this->assertTrue($git->successful());
    }

    public function testAddRemote()
    {
        $git = new Git();

        $this->assertInstanceOf(Git::class, $git->addRemote('adhocore', 'phint'));
        $this->assertFalse($git->successful());
    }
}
