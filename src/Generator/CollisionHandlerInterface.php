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
