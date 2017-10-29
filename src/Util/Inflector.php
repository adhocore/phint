<?php

namespace Ahc\Phint\Util;

class Inflector
{
    /**
     * StuldyCase.
     *
     * @param string $path
     *
     * @return string
     */
    public function stuldyCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    /**
     * Separate words.
     *
     * @param string $path
     *
     * @return string
     */
    public function words($string)
    {
        return ucwords(str_replace(['-', '_'], ' ', $string));
    }
}
