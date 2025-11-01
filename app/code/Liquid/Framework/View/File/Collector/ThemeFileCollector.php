<?php
declare(strict_types=1);

namespace Liquid\Framework\View\File\Collector;

use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;
use Liquid\Framework\Filesystem\Directory\DirectoryRead;
use Liquid\Framework\Filesystem\Type\FileType;
use Liquid\Framework\View\Design\Theme;
use Liquid\Framework\View\File\File;

/**
 * Source of view files introduced by a theme
 */
class ThemeFileCollector implements ThemeFileCollectorInterface
{
    private string $subDir;

    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar,
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
        $themePath = $theme->getFullPath();
        if (empty($themePath)) {
            return [];
        }
        $themeAbsolutePath = $this->componentRegistrar->getPath(ComponentType::Theme, $themePath);
        if (!$themeAbsolutePath) {
            return [];
        }
        $themeDir = new DirectoryRead(new FileType(), $themeAbsolutePath);
        $files = $themeDir->search($this->subDir . $filePath);
        \var_dump($files);
        $result = [];
        foreach ($files as $file) {

            $filename = $themeDir->getAbsolutePath($file);
            $result[] = new File($filename, null, $theme);
        }
        return $result;
    }
}
