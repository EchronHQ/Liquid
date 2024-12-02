<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\Theme;

use Liquid\Framework\Component\ComponentRegistrarInterface;
use Liquid\Framework\Component\ComponentType;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Design\Theme;

class ThemeProvider
{
    private array|null $themes = null;

    public function __construct(
        private readonly ObjectManagerInterface      $objectManager,
        private readonly ComponentRegistrarInterface $componentRegistrar
    )
    {
//        $theme0 = $this->objectManager->create(Theme::class);
//        $theme0->code = 'Liquid_Default';
//
//
//        $theme0->fullPath = '';
//        $this->themes = [
//            0 => new Theme(),
//        ];
    }

    /**
     * Get theme by id
     */
    public function getThemeById(int|string $themeId): Theme|null
    {
        if ($this->themes === null) {
            $this->loadThemes();
        }
        if (isset($this->themes[$themeId])) {
            return $this->themes[$themeId];
        }
        return null;
    }

    private function loadThemes(): void
    {
        $registeredThemes = $this->componentRegistrar->getPaths(ComponentType::Theme);
        foreach ($registeredThemes as $themeId => $themePath) {
            $theme = $this->objectManager->create(Theme::class);
            $theme->code = $themeId;
            $theme->fullPath = $themePath;

            if ($themeId === 'Liquid_Default') {
                $theme->parentId = null;
            }
            if ($themeId === 'Attlaz_Default') {
                $theme->parentId = 'Liquid_Default';
            }
            $this->themes[$theme->code] = $theme;
        }

    }

    public function getThemeByFullPath(string $path): Theme|null
    {
        throw new \Error('Not implemented');
        return null;
    }
}
