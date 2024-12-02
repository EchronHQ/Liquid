<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Element;

interface BlockInterface
{
    public function toHtml(): string;
}
