<?php
declare(strict_types=1);

namespace Liquid\Framework\View\File\Collector;

use Liquid\Framework\Component\ComponentType;
use Liquid\Framework\Component\DirectorySearch;
use Liquid\Framework\Exception\ContextException;
use Liquid\Framework\Exception\FileSystemException;
use Liquid\Framework\View\Design\Theme;
use Liquid\Framework\View\File\File;

/**
 * Source of files introduced by modules
 */
class ModuleFileCollector implements ThemeFileCollectorInterface
{
    private string $subDir;

    public function __construct(
        private readonly DirectorySearch $componentDirectorySearch,
        string                           $subDir = ''
    )
    {
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    public function setSubDir(string $subDir): void
    {
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    /**
     * @param Theme $theme
     * @param string $filePath
     * @return File[]
     * @throws FileSystemException|ContextException
     */
    public function getFiles(Theme $theme, string $filePath): array
    {
        $result = [];
        // TODO: base files are used in any scope - should we rename the directory to 'global'?
        $baseThemeFiles = $this->componentDirectorySearch->collectFilesWithContext(
            ComponentType::Module,
            "view/base/{$this->subDir}{$filePath}"
        );
        foreach ($baseThemeFiles as $file) {
            $result[] = new File($file->getFullPath(), $file->getComponentId(), null, true);
        }

        $area = $theme->getArea()->value;
        $areaThemeFiles = $this->componentDirectorySearch->collectFilesWithContext(
            ComponentType::Module,
            "view/{$area}/{$this->subDir}{$filePath}"
        );
        foreach ($areaThemeFiles as $file) {
            $result[] = new File($file->getFullPath(), $file->getComponentId());
        }
        return $result;
    }
}
