<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution;

use Liquid\Framework\View\Design\FileResolution\Types\ResolveTypesPool;

class TemplateFile extends File
{
    protected function getFallbackType(): string
    {
        return ResolveTypesPool::TYPE_TEMPLATE_FILE;
    }

}
