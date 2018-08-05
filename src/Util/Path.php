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

use Ahc\Json\Comment;

class Path
{
    /** @var string */
    protected $phintPath;

    /**
     * Platform agnostic absolute path detection.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isAbsolute(string $path): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return strpos($path, ':') === 1;
        }

        return isset($path[0]) && $path[0] === '/';
    }

    public function getRelativePath($fullPath, $basePath): string
    {
        if (strpos($fullPath, $basePath) === 0) {
            return substr($fullPath, strlen($basePath));
        }

        // Hmm!
        return $fullPath;
    }

    public function ensureDir(string $dir): bool
    {
        if (!\is_dir($dir)) {
            return \mkdir($dir, 0777, true);
        }

        return true;
    }

    public function getExtension(string $filePath): string
    {
        return \pathinfo($filePath, \PATHINFO_EXTENSION);
    }

    public function readAsJson(string $filePath, bool $asArray = true)
    {
        return (new Comment)->decode(\file_get_contents($filePath), $asArray);
    }

    public function writeFile(string $file, $content, int $mode = null): bool
    {
        $this->ensureDir(\dirname($file));

        if (!\is_string($content)) {
            $content = \json_encode($content, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
        }

        return \file_put_contents($file, $content, $mode) > 0;
    }

    public function getPhintPath(string $subpath = ''): string
    {
        $this->initPhintPath();

        if ($subpath && $this->phintPath) {
            return $this->phintPath . '/' . \ltrim($subpath, '/');
        }

        return $this->phintPath;
    }

    protected function initPhintPath()
    {
        if (null !== $this->phintPath) {
            return;
        }

        $this->phintPath = '';

        if (false !== $home = ($_SERVER['HOME'] ?? \getenv('HOME'))) {
            $path = \rtrim($home, '/') . '/.phint';

            if ($this->ensureDir($path)) {
                $this->phintPath = $path;
            }
        }
    }
}
