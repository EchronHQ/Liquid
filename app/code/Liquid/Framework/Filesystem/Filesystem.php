<?php
declare(strict_types=1);

namespace Liquid\Framework\Filesystem;

use Liquid\Framework\Filesystem\Directory\DirectoryRead;
use Liquid\Framework\Filesystem\Type\FileType;

class Filesystem
{
    private array $readInstances = [];

    public function __construct(private readonly DirectoryList $directoryList)
    {

    }

    /**
     * Create an instance of directory with read permissions
     *
     * @param Path $directoryCode
     * @return DirectoryRead
     * @throws \Exception
     */
    public function getDirectoryRead(Path $directoryCode): DirectoryRead
    {
        $code = $directoryCode->name . '-file';
        if (!array_key_exists($code, $this->readInstances)) {
            $path = $this->getDirPath($directoryCode);

            $this->readInstances[$code] = new DirectoryRead(new FileType(), $path);
        }
        return $this->readInstances[$code];
    }

    /**
     * Gets configuration of a directory
     *
     * @param Path $code
     * @return string
     * @throws \Exception
     */
    private function getDirPath(Path $code): string
    {
        return $this->directoryList->getPath($code);
    }
}
