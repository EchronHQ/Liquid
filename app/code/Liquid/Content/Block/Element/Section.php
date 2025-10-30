<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Framework\View\Element\Template;

class Section extends Template
{
    protected string|null $template = 'Liquid_Content::element/template.phtml';
}
