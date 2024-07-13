<?php
declare(strict_types=1);

namespace Liquid\Core\Helper;

class IdHelper
{
    public static function escapeId(string $input): string
    {
        return \str_replace(['/', '//'], '_', $input);
    }
}
