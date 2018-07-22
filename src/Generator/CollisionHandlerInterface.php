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
    public function handle(string $targetFile, string $newContent, array $parameters = null);
}
