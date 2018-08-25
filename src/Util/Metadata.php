<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Util;

use CrazyFactory\DocBlocks\DocBlock;

class Metadata
{
    public function forClass(string $classFqcn, bool $docblock = false): array
    {
        return $this->forReflectionClass(new \ReflectionClass($classFqcn), $docblock);
    }

    public function forReflectionClass(\ReflectionClass $class, bool $docblock = false): array
    {
        $metadata = [
            'classFqcn'   => $classFqcn,
            'classPath'   => $class->getFilePath(),
            'name'        => $class->getShortName(),
            'isTrait'     => $class->isTrait(),
            'isAbstract'  => $class->isAbstract(),
            'isInterface' => $class->isInterface(),
            'newable'     => $class->isInstantiable(),
        ];

        if ($docblock) {
            $texts = (new DocBlock($class))->texts();
            $title = \array_shift($texts);

            $metadata += \compact('title', 'texts');
        }

        $metadata['methods'] = [];

        foreach ($class->getMethods() as $method) {
            if ($method->class !== $classFqcn) {
                continue;
            }

            $metadata['methods'][$method->name] = $this->forReflectionMethod($method, $docblock);
        }

        return $metadata;
    }
}
