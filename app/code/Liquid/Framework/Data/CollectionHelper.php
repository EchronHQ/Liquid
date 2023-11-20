<?php
declare(strict_types=1);

namespace Liquid\Framework\Data;

class CollectionHelper
{
    public static function shuffleAndLimit(array $input, bool $shuffle, int|null $limit): array
    {
        if ($shuffle) {
            shuffle($input);
        }
        if ($limit !== null && count($input) > $limit) {
            $input = array_slice($input, 0, $limit);
        }
        return $input;
    }
}
