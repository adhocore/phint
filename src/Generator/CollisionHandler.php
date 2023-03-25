<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Generator;

use Ahc\Phint\Util\Arr;
use Ahc\Phint\Util\Path;

class CollisionHandler implements CollisionHandlerInterface
{
    public function __construct(Path $pathUtil = null)
    {
        $this->pathUtil = $pathUtil ?: new Path;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $targetFile, string $newContent, array $parameters = null): bool
    {
        switch ($this->pathUtil->getExtension($targetFile)) {
            case 'json':
                return $this->mergeJson($targetFile, $newContent);

            case 'md':
                return $this->appendFile($targetFile, "\n---\n" . $newContent);
        }

        return false;
    }

    protected function mergeJson(string $targetFile, string $newContent): bool
    {
        $oldJson = $this->pathUtil->readAsJson($targetFile);
        $newJson = \json_decode($newContent, true);
        $merged  = Arr::mergeRecursive($oldJson, $newJson);

        return $this->pathUtil->writeFile($targetFile, $merged);
    }

    protected function appendFile(string $targetFile, string $newContent): bool
    {
        return $this->pathUtil->writeFile($targetFile, $newContent, \FILE_APPEND);
    }
}
