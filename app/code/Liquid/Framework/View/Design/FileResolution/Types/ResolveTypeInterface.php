<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

interface ResolveTypeInterface
{
    /**
     * Get ordered list of folders to search for a file
     *
     * @param array $params Values to substitute placeholders with
     * @return array folders to perform a search
     */
    public function getPatternDirs(array $params): array;
}
