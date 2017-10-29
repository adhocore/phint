<?php

namespace Ahc\Phint\Generator;

interface GeneratorInterface
{
    public function generate($targetPath, array $parameters);
}
