<?php
declare(strict_types=1);

namespace Liquid\Framework\Filesystem;

use Liquid\Framework\Exception\FileSystemException;

class FileDriver
{
    /**
     * Returns last warning message string
     *
     * @return string
     */
    private function getWarningMessage(): string|null
    {
        $warning = error_get_last();
        if ($warning && $warning['type'] == E_WARNING) {
            return 'Warning!' . $warning['message'];
        }
        return null;
    }

    /**
     * Is file or directory exist in file system
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function isExists($path)
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
}
