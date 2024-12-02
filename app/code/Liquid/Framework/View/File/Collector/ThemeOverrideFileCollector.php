<?php
declare(strict_types=1);

namespace Liquid\Framework\View\File\Collector;

use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;
use Liquid\Framework\Filesystem\Directory\DirectoryRead;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Design\Theme;
use Liquid\Framework\View\File\File;
use Liquid\Framework\View\Helper\PathPatternHelper;

/**
 * Source of view files that explicitly override base files introduced by modules.
 * These are for example files defined in the main theme
 */
class ThemeOverrideFileCollector implements ThemeFileCollectorInterface
{
    private string $subDir;

    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar,
        private readonly ObjectManagerInterface      $objectManager,
        private readonly PathPatternHelper           $pathPatternHelper,
        string                                       $subDir = ''
    )
    {
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    public function setSubDir(string $subDir): void
    {
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    /** @inheritdoc */
    public function getFiles(Theme $theme, string $filePath): array
    {
        $namespace = $module = '*';
        $themePath = $theme->getFullPath();
        if (empty($themePath)) {
            return [];
        }
        $themeAbsolutePath = $this->componentRegistrar->getPath(ComponentType::Theme, $themePath);
        if (!$themeAbsolutePath) {
            return [];
        }
        /** @var DirectoryRead $themeDir */
        $themeDir = $this->objectManager->create(DirectoryRead::class, [
            'path' => $themeAbsolutePath,
        ]);
        $searchPattern = "{$namespace}_{$module}/{$this->subDir}{$filePath}";
        $files = $themeDir->search($searchPattern);
        $result = [];
        $pattern = "#(?<moduleName>[^/]+)/{$this->subDir}"
            . $this->pathPatternHelper->translatePatternFromGlob($filePath) . "$#i";
        foreach ($files as $file) {
            $filename = $themeDir->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $result[] = new File($filename, $matches['moduleName']);
        }
        return $result;
    }
}
