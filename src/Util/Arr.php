<?php

namespace Ahc\Phint\Util;

class Arr
{
    /**
     * @see http://php.net/array_merge_recursive#92195
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public static function mergeRecursive(array $array1, array $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (\is_array($value) && isset($merged[$key]) && \is_array($merged[$key])) {
                $merged[$key] = self::mergeRecursive($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}
