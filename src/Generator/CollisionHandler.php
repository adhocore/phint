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
    public function handle(string $targetFile, string $newContent, array $parameters = null)
    {
        switch ($this->pathUtil->getExtension($targetFile)) {
            case 'json':
                $this->mergeJson($targetFile, $newContent);
                break;

            case 'md':
                $this->appendFile($targetFile, "\n---\n" . $newContent);
                break;
        }
    }

    protected function mergeJson(string $targetFile, string $newContent)
    {
        $oldJson = $this->pathUtil->readAsJson($targetFile);
        $newJson = \json_decode($newContent, true);
        $merged  = Arr::mergeRecursive($oldJson, $newJson);

        $this->pathUtil->writeFile($targetFile, $merged);
    }

    protected function appendFile(string $targetFile, string $newContent)
    {
        $this->pathUtil->writeFile($targetFile, $newContent, \FILE_APPEND);
    }
}
