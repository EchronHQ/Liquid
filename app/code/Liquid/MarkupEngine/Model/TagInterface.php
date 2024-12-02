<?php
declare(strict_types=1);

namespace Liquid\MarkupEngine\Model;

interface TagInterface
{
    /**
     * @return MarkupTagAttribute[]
     */
    public function getAttributes(): array;
}
