<?php
declare(strict_types=1);

namespace Liquid\MarkupEngine\Model;

class MarkupTagAttribute
{
    public string $validation;

    public function __construct(
        public string $attribute,
        public string $type,
        public bool   $required = false
    )
    {
    }
}
