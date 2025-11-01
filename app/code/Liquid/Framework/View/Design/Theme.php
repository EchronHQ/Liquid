<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\State;
use Liquid\Framework\DataObject;
use Liquid\Framework\Exception\ContextException;
use Liquid\Framework\View\Design\Theme\ThemeProvider;

class Theme extends DataObject
{
    public string $areaCode;

    public string|null $parentId = null;
    public string|null $fullPath = null;
    public string $code;

    protected Theme|null $_parentTheme = null;
    private array|null $inheritanceSequence = null;

    public function __construct(
        private readonly ThemeProvider $themeProvider,
        private readonly State         $appState
    )
    {
        parent::__construct();
    }

    /**
     * Return the full theme inheritance sequence, from the root theme till a specified one
     * @return Theme[]
     */
    public function getInheritedThemes(): array
    {
        if (null === $this->inheritanceSequence) {
            $theme = $this;
            $result = [];
            while ($theme) {
                $result[] = $theme;
                $theme = $theme->getParentTheme();
            }
            $this->inheritanceSequence = \array_reverse($result);
        }
        return $this->inheritanceSequence;
    }

    /**
     * Retrieve parent theme instance
     *
     * @return Theme|null
     */
    public function getParentTheme(): Theme|null
    {
        if ($this->hasData('parent_theme')) {
            return $this->getData('parent_theme');
        }
        $theme = null;
        if ($this->parentId !== null) {
            $theme = $this->themeProvider->getThemeById($this->parentId);
        }
        $this->_parentTheme = $theme;
        return $theme;
    }


    /**
     * Retrieve theme full path which is used to distinguish themes if they are not in DB yet
     *
     * Alternative id looks like "<area>/<theme_path>".
     * Used as id in file-system theme collection
     *
     * @return string|null
     * @throws ContextException
     */
    public function getFullPath(): string|null
    {
        if ($this->fullPath !== null) {
            return $this->fullPath;
        }
//        return $this->fullPath;
        return $this->getThemePath() ? $this->getArea()->value . DIRECTORY_SEPARATOR . $this->getThemePath() : null;
    }

    /**
     * Retrieve theme path unique within an area
     */
    public function getThemePath(): string|null
    {
        return $this->getData('theme_path');
    }

    /**
     * Retrieve code of an area a theme belongs to
     *
     * @return AreaCode
     * @throws ContextException
     */
    public function getArea(): AreaCode
    {
        // In order to support environment emulation of area, if area is set, return it
//        if ($this->getData('area')) {
//            return $this->getData('area');
//        }
        return $this->appState->getAreaCode();
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
