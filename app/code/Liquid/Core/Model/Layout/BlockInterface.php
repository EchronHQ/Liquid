<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Layout;

interface BlockInterface
{
    public function toHtml(): string;
}
