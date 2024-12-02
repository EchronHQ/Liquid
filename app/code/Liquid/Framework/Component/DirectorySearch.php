<?php
declare(strict_types=1);

namespace Liquid\Framework\Component;

use Liquid\Framework\Exception\FileSystemException;
use Liquid\Framework\Filesystem\Directory\DirectoryRead;
use Liquid\Framework\Filesystem\Type\FileType;

class DirectorySearch
{
    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar,
    )
    {

    }

    /**
     * Search for files in each component by pattern, returns absolute paths
     *
     * @param ComponentType $componentType
     * @param string $pattern
     * @return string[]
     */
    public function collectFiles(ComponentType $componentType, string $pattern): array
    {
        return $this->collect($componentType, $pattern, false);
    }

    /**
     * Collect files in components
     * If $withContext is true, returns array of file objects with component context
     *
     * @param ComponentType $componentType
     * @param string $pattern
     * @param bool|false $withContext
     * @return ComponentFile[]|string[]
     */
    private function collect(ComponentType $componentType, string $pattern, bool $withContext): array
    {
        $files = [];
        foreach ($this->componentRegistrar->getPaths($componentType) as $componentName => $path) {
            // TODO: use shared file pointer pool
            $directoryRead = new DirectoryRead(new FileType(), $path);
            $foundFiles = $directoryRead->search($pattern);
            foreach ($foundFiles as $foundFile) {
                $foundFile = $directoryRead->getAbsolutePath($foundFile);
                if ($withContext) {
                    $files[] = new ComponentFile($componentType, $componentName, $foundFile);
                } else {
                    $files[] = $foundFile;
                }
            }
        }
        return $files;
    }

    /**
     * Search for files in each component by pattern, returns file objects with absolute file paths
     *
     * @param ComponentType $componentType
     * @param string $pattern
     * @return ComponentFile[]
     * @throws FileSystemException
     */
    public function collectFilesWithContext(ComponentType $componentType, string $pattern): array
    {
        return $this->collect($componentType, $pattern, true);
    }

}
