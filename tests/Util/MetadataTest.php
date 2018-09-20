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

use Ahc\Phint\Util\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    public function testForClass()
    {
        $metaData = new Metadata;
        $result   = $metaData->forClass(Metadata::class);

        $this->assertArraySubset([
            'namespace'   => 'Ahc\Phint\Util',
            'classFqcn'   => 'Ahc\Phint\Util\Metadata',
            'name'        => 'Metadata',
            'className'   => 'Metadata',
            'isTrait'     => false,
            'isAbstract'  => false,
            'isInterface' => false,
            'newable'     => true,
            'title'       => null,
            'texts'       => [],
            'methods'     => [],
            'name'        => 'Metadata',
        ], $result);
    }

    public function testForMethod()
    {
        $metaData = new Metadata;
        $result   = $metaData->forMethod(Metadata::class, 'forClass');

        $this->assertSame([
            'name'       => 'forClass',
            'inClass'    => 'Ahc\Phint\Util\Metadata',
            'isStatic'   => false,
            'isFinal'    => false,
            'isPublic'   => true,
            'isAbstract' => false,
            'maybeMagic' => false,
            'throws'     => [],
            'title'      => null,
            'texts'      => [],
            'params'     => [
                ['string $classFqcn', ''],
            ],
            'return' => ['array', ''],
        ], $result);
    }
}
