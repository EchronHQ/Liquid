<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Asset\File;

use Liquid\Framework\App\Area\AreaCode;

class AssetFileFallbackContext
{
    public function __construct(
        $baseUrl,
        private AreaCode $areaType,
        private string $themePath,
        private string $localeCode
    )
    {


        // parent::__construct($baseUrl, DirectoryList::STATIC_VIEW, $this->generatePath());
    }

    /**
     * Get area code
     */
    public function getAreaCode(): AreaCode
    {
        return $this->areaType;
    }

    /**
     * Get theme path
     */
    public function getThemePath(): string
    {
        return $this->themePath;
    }

    /**
     * Get locale code
     */
    public function getLocale(): string
    {
        return $this->localeCode;
    }

    /**
     * Generate path based on the context parameters
     *
     * @return string
     */
    private function generatePath(): string
    {
        return $this->areaType->value .
            ($this->themePath ? '/' . $this->themePath : '') .
            ($this->localeCode ? '/' . $this->localeCode : '');
    }
}
