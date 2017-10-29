<?php

namespace Ahc\Phint\Generator;

interface CollisionHandlerInterface
{
    /**
     * Skip/override/merge existing files in targetFile.
     *
     * @param string $targetFile
     * @param string $newContent
     * @param array  $parameters
     *
     * @return void
     */
    public function handle($targetFile, $newContent, array $parameters = null);
}
