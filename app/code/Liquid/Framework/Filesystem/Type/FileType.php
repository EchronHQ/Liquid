<?php
declare(strict_types=1);

namespace Liquid\Framework\Filesystem\Type;

use Laminas\Stdlib\Glob as LaminasGlob;
use Liquid\Framework\Exception\FileSystemException;

class FileType
{
    private bool $stateful = false;

    /**
     * Retrieve file contents from given path
     *
     * @param string $path
     * @param string|null $flag
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     */
    public function fileGetContents(string $path, bool $useIncludePath = false, mixed $context = null): string
    {
        $filename = $this->getScheme() . $path;

        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $result = @file_get_contents($filename, $useIncludePath, $context);

        if (false === $result) {
            throw new FileSystemException('The contents from the "' . $path . '" file can\'t be read. ' . $this->getWarningMessage() . '.');
        }
        return $result;
    }

    /**
     * Return path with scheme
     *
     * @param null|string $scheme
     * @return string
     */
    private function getScheme(string|null $scheme = null): string
    {
        return $scheme ? $scheme . '://' : '';
    }

    /**
     * Returns last warning message string
     *
     * @return string|null
     */
    protected function getWarningMessage(): string|null
    {
        $warning = error_get_last();
        if ($warning && $warning['type'] === E_WARNING) {
            return 'Warning!' . $warning['message'];
        }
        return null;
    }

    /**
     * Search paths by given regex
     *
     * @param string $pattern
     * @param string $path
     * @return string[]
     */
    public function search(string $pattern, string $path): array
    {
        if (!$this->stateful) {
            clearstatcache();
        }
        $globPattern = rtrim((string)$path, '/') . '/' . ltrim((string)$pattern, '/');
        $result = LaminasGlob::glob($globPattern, LaminasGlob::GLOB_BRACE);

        // var_dump($globPattern);
        return is_array($result) ? $result : [];
    }

    /**
     * Returns an absolute path for the given one.
     *
     * @param string $basePath
     * @param string $path
     * @param string|null $scheme
     * @return string
     */
    public function getAbsolutePath(string $basePath, string $path, string|null $scheme = null): string
    {
        // check if the path given is already an absolute path containing the
        // basepath. so if the basepath starts at position 0 in the path, we
        // must not concatinate them again because path is already absolute.
        $path = $path !== null ? $path : '';
        if ('' !== $basePath && strpos($path, (string)$basePath) === 0) {
            return $this->getScheme($scheme) . $path;
        }

        return $this->getScheme($scheme) . $basePath . ltrim($this->fixSeparator($path), '/');
    }

    /**
     * Fixes path separator.
     *
     * Utility method.
     *
     * @param string $path
     * @return string
     */
    private function fixSeparator(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Retrieves relative path
     *
     * @param string $basePath
     * @param string|null $path
     * @return string
     */
    public function getRelativePath(string $basePath, string|null $path = null): string
    {
        $path = $path !== null ? $this->fixSeparator($path) : '';
        if ($basePath === null || strpos($path, $basePath) === 0 || $basePath == $path . '/') {
            $result = substr($path, strlen($basePath));
        } else {
            $result = $path;
        }
        return $result;
    }

    /**
     * Is file or directory exist in file system
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function isExists(string $path): bool
    {
        $filename = $this->getScheme() . $path;
        if (!$this->stateful) {
            clearstatcache(false, $filename);
        }
        $result = @file_exists($filename);
        if ($result === null) {
            throw new FileSystemException('An error occurred during "' . $this->getWarningMessage() . '" execution.');
        }
        return $result;
    }

    /**
     * Return correct path for link
     *
     * @param string $path
     * @return mixed
     */
    public function getRealPathSafety(string $path): string
    {
        if ($path === null) {
            return '';
        }

        //Check backslashes
        $path = preg_replace(
            '/\\\\+/',
            DIRECTORY_SEPARATOR,
            $path
        );

        //Removing redundant directory separators.
        $path = preg_replace(
            '/\\' . DIRECTORY_SEPARATOR . '\\' . DIRECTORY_SEPARATOR . '+/',
            DIRECTORY_SEPARATOR,
            $path
        );

        if (strpos($path, DIRECTORY_SEPARATOR . '.') === false) {
            return rtrim($path, DIRECTORY_SEPARATOR);
        }

        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        if (end($pathParts) == '.') {
            $pathParts[count($pathParts) - 1] = '';
        }
        $realPath = [];
        foreach ($pathParts as $pathPart) {
            if ($pathPart == '.') {
                continue;
            }
            if ($pathPart == '..') {
                array_pop($realPath);
                continue;
            }
            $realPath[] = $pathPart;
        }

        return rtrim(implode(DIRECTORY_SEPARATOR, $realPath), DIRECTORY_SEPARATOR);
    }
}
