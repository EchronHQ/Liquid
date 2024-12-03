<?php
declare(strict_types=1);

namespace Liquid\Framework\Filesystem;

use Liquid\Framework\Exception\FileSystemException;

class FileDriver
{
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
     * Return path with scheme
     *
     * @param null|string $scheme
     * @return string
     */
    protected function getScheme(string|null $scheme = null): string
    {
        return $scheme ? $scheme . '://' : '';
    }

    /**
     * Returns last warning message string
     */
    private function getWarningMessage(): string|null
    {
        $warning = error_get_last();
        if ($warning && $warning['type'] === E_WARNING) {
            return 'Warning!' . $warning['message'];
        }
        return null;
    }
}
