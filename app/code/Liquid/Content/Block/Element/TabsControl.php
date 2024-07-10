<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Content\Block\TemplateBlock;

class TabsControl extends TemplateBlock
{
    public string|null $template = 'tabscontrol.phtml';

    public int $selectedIndex = 0;
    public array $tabs = [
        ['title' => 'A', 'content' => 'A Your hub for important work'],
        ['title' => 'B', 'content' => 'B Your hub for important work'],
        ['title' => 'C', 'content' => 'C Your hub for important work'],
    ];

}
