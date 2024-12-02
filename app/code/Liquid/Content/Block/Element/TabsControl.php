<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Framework\View\Element\ArgumentInterface;

class TabsControl implements ArgumentInterface
{


    public int $selectedIndex = 0;
    public array $tabs = [
        ['title' => 'A', 'content' => 'A Your hub for important work'],
        ['title' => 'B', 'content' => 'B Your hub for important work'],
        ['title' => 'C', 'content' => 'C Your hub for important work'],
    ];

}
