<?php
declare(strict_types=1);

namespace Liquid\Framework\View\File\Collector;

use Liquid\Framework\View\Design\Theme;
use Liquid\Framework\View\File\File;

interface ThemeFileCollectorInterface
{
    /**
     * @param Theme $theme
     * @param string $filePath
     * @return File[]
     */
    public function getFiles(Theme $theme, string $filePath): array;
}
