<?php

namespace Ahc\Phint\Util;

class Path
{
    /**
     * Platform agnostic absolute path detection.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isAbsolute($path)
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return strpos($path, ':') === 1;
        }

        return isset($path[0]) && $path[0] === '/';
    }

    public function getRelativePath($fullPath, $basePath)
    {
        if (strpos($fullPath, $basePath) === 0) {
            return substr($fullPath, strlen($basePath));
        }

        // Hmm!
        return $fullPath;
    }
}
