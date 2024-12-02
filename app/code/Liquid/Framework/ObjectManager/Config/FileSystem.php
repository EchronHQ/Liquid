<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager\Config;

use Liquid\Framework\Config\FileResolverInterface;
use Liquid\Framework\Config\FileSystemReader;

class FileSystem extends FileSystemReader
{
    public function __construct(FileResolverInterface $fileResolver, string $fileName = 'di.php')
    {
        parent::__construct($fileResolver, $fileName);
    }
}
