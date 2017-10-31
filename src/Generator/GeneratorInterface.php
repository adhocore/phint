<?php

namespace Ahc\Phint\Generator;

interface GeneratorInterface
{
    /**
     * Generate basic project files into target path using given parameters.
     *
     * @param string                    $targetPath
     * @param array                     $parameters
     * @param CollisionHandlerInterface $handler    Optional, if not provided files are overwritten.
     *
     * @return void
     */
    public function generate($targetPath, array $parameters, CollisionHandlerInterface $handler = null);
}
