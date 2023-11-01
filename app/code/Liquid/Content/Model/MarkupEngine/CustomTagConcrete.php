<?php

declare(strict_types=1);

namespace Liquid\Content\Model\MarkupEngine;

class CustomTagConcrete extends CustomTag
{
    public function render(): string
    {
        return $this->contentHtml;
    }
}
