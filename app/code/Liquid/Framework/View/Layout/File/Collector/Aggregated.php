<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout\File\Collector;

use Liquid\Framework\View\Design\Theme;
use Liquid\Framework\View\File\Collector\ModuleFileCollector;
use Liquid\Framework\View\File\Collector\ThemeFileCollector;
use Liquid\Framework\View\File\Collector\ThemeFileCollectorInterface;
use Liquid\Framework\View\File\Collector\ThemeOverrideFileCollector;
use Liquid\Framework\View\File\FileList;

class Aggregated implements ThemeFileCollectorInterface
{
    public function __construct(
        private readonly ModuleFileCollector        $moduleFileCollector,
        private readonly ThemeFileCollector $themeFileCollector,
        private readonly ThemeOverrideFileCollector $themeOverrideFileCollector
    )
    {
        $this->moduleFileCollector->setSubDir('layout');
        $this->themeFileCollector->setSubDir('layout');
        $this->themeOverrideFileCollector->setSubDir('layout');
    }

    /**
     * @inheritdoc
     */
    public function getFiles(Theme $theme, string $filePath): array
    {
        $list = new FileList();
        $list->add($this->moduleFileCollector->getFiles($theme, $filePath));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $list->add($this->themeFileCollector->getFiles($currentTheme, $filePath));
//            $list->replace($this->overrideBaseFiles->getFiles($currentTheme, $filePath));
//            $list->replace($this->overrideThemeFiles->getFiles($currentTheme, $filePath));
            $list->replace($this->themeOverrideFileCollector->getFiles($currentTheme, $filePath));
        }
        return $list->getAll();
    }
}
