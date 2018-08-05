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

class Inflector
{
    /**
     * StuldyCase.
     *
     * @param string $path
     *
     * @return string
     */
    public function stuldyCase(string $string): string
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
    public function words(string $string): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $string));
    }

    /**
     * Snakeize.
     *
     * @param string $path
     *
     * @return string
     */
    public function snakeCase(string $string): string
    {
        $string = \str_replace([' ', '-'], '_', $string);

        return \ltrim(\strtolower(\preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $string)), '_');
    }
}
