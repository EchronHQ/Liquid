<?php
declare(strict_types=1);

namespace Liquid\Framework\Config;

use Liquid\Framework\Filesystem\Filesystem;
use Liquid\Framework\Filesystem\Path;
use Liquid\Framework\Module\File\Reader;

class FileResolver implements FileResolverInterface
{
    public function __construct(
        private readonly Reader     $moduleFileReader,
        private readonly Filesystem $filesystem
    )
    {

    }

    public function get(string $filename, string|null $scope = null): FileIterator
    {
        switch ($scope) {
            case 'primary':
                $directory = $this->filesystem->getDirectoryRead(Path::CONFIG);
                $absolutePaths = [];
                foreach ($directory->search('{' . $filename . ',*/' . $filename . '}') as $path) {
                    $absolutePaths[] = $directory->getAbsolutePath($path);
                }
                return new FileIterator($absolutePaths);
            case 'global':
                return $this->moduleFileReader->getConfigurationFiles($filename);
            default:
                return $this->moduleFileReader->getConfigurationFiles($scope . '/' . $filename);
        }

    }
}
