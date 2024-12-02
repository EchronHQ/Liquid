<?php
declare(strict_types=1);

namespace Liquid\Framework\Filesystem\File;

use Liquid\Framework\Exception\FileSystemException;
use Liquid\Framework\Filesystem\Type\FileType;

class FileRead
{
    public function __construct(
        private readonly FileType $type,
        private readonly string   $path
    )
    {

    }

    /**
     * Return file content
     *
     * @param bool $useIncludePath
     * @param resource|null $context
     * @return string
     * @throws FileSystemException
     */
    public function readAll(bool $useIncludePath = false, mixed $context = null): string
    {
        return $this->type->fileGetContents($this->path, $useIncludePath, $context);
    }
}
