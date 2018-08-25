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
        $methods = [];

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $m) {
            if ($m->class !== $classFqcn) {
                continue;
            }

            // $methods[$m->name] = $this->getMethodMetadata($m);
        }

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

        $metadata['methods'] = $methods;

        return $metadata;
    }
}
