<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;

use Liquid\Framework\Config\FileIterator;
use Liquid\Framework\Config\FileResolverInterface;
use Liquid\Framework\Filesystem\Directory\DirectoryRead;
use Liquid\Framework\Filesystem\Filesystem;
use Liquid\Framework\Filesystem\Path;

class PrimaryConfigFileResolver implements FileResolverInterface
{
    private DirectoryRead $configDirectory;

    public function __construct(Filesystem $filesystem)
    {
        $this->configDirectory = $filesystem->getDirectoryRead(Path::CONFIG);
    }

    /**
     * Retrieve the list of configuration files with given name that relate to specified scope
     *
     * @param string $filename
     * @param string|null $scope
     * @return FileIterator
     */
    public function get(string $filename, string|null $scope = null): FileIterator
    {
        $configPaths = $this->configDirectory->search('{*' . $filename . ',*/*' . $filename . '}');

        $configAbsolutePaths = [];
        foreach ($configPaths as $configPath) {
            $configAbsolutePaths[] = $this->configDirectory->getAbsolutePath($configPath);
        }
        return new FileIterator($configAbsolutePaths);
    }
}
