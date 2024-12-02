<?php
declare(strict_types=1);

namespace Liquid\Framework\Filesystem\Directory;

use Liquid\Framework\Exception\FileSystemException;
use Liquid\Framework\Filesystem\Type\FileType;

class DirectoryRead
{
    private string $path;

    public function __construct(
        private readonly FileType $type,
        string                    $path
    )
    {
        $this->setPath($path);
    }

    /**
     * Sets base path
     *
     * @param string $path
     * @return void
     */
    protected function setPath(string $path): void
    {
        if (!empty($path)) {
            $this->path = rtrim(str_replace('\\', '/', $path), '/') . '/';
        }
    }

    /**
     * Search all entries for given regex pattern
     *
     * @param string $pattern
     * @param string|null $path [optional]
     * @return string[]
     */
    public function search(string $pattern, string|null $path = null): array
    {
        if ($path) {
            $absolutePath = $this->type->getAbsolutePath($this->path, $this->getRelativePath($path));
        } else {
            $absolutePath = $this->path;
        }
        $files = $this->type->search($pattern, $absolutePath);
        $result = [];
        foreach ($files as $file) {
            $result[] = $this->getRelativePath($file);
        }
        return $result;
    }

    /**
     * Retrieves absolute path i.e. /var/www/application/file.txt
     *
     * @param string|null $path
     * @param string|null $scheme
     * @return string
     */
    public function getAbsolutePath(string|null $path = null, string|null $scheme = null): string
    {
        //$this->validatePath($path, $scheme);

        return $this->type->getAbsolutePath($this->path, $path, $scheme);
    }

    /**
     * Retrieves relative path
     *
     * @param string|null $path
     * @return string
     */
    public function getRelativePath(string|null $path = null): string
    {
//        $this->validatePath(
//            $path,
//            null,
//            $path && $path[0] === DIRECTORY_SEPARATOR
//        );

        return $this->type->getRelativePath($this->path, $path);
    }

    /**
     * Check a file or directory exists
     *
     * @param string|null $path [optional]
     * @return bool
     * @throws FileSystemException
     */
    public function isExist(string|null $path = null): bool
    {
        // $this->validatePath($path);

        return $this->type->isExists(
            $this->type->getRealPathSafety($this->type->getAbsolutePath($this->path, $path))
        );
    }
}
