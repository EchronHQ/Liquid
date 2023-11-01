<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Content\Block\TemplateBlock;

class Section extends TemplateBlock
{
    protected string|null $template = 'Liquid_Content::element/template.phtml';
}
