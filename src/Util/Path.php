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

    public function ensureDir($dir)
    {
        if (!\is_dir($dir)) {
            \mkdir($dir, 0777, true);
        }
    }

    public function getExtension($filePath)
    {
        return pathinfo($filePath, PATHINFO_EXTENSION);
    }

    public function readAsJson($filePath, $asArray = true)
    {
        return json_decode(file_get_contents($filePath), true) ?: [];
    }

    public function writeFile($file, $content, $mode = null)
    {
        file_put_contents($file, $content, $mode);
    }
}
