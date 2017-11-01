<?php

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
    public function handle($targetFile, $newContent, array $parameters = null)
    {
        switch ($this->pathUtil->getExtension($targetFile)) {
            case 'json':
                $this->mergeJson($targetFile, $newContent);

                break;
        }
    }

    protected function mergeJson($targetFile, $newContent)
    {
        $oldJson = $this->pathUtil->readAsJson($targetFile);
        $newJson = \json_decode($newContent, true);
        $merged  = Arr::mergeRecursive($oldJson, $newJson);

        $this->pathUtil->writeFile(
            $targetFile,
            \json_encode($merged, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES) . "\n"
        );
    }
}
