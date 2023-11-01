<?php

declare(strict_types=1);

namespace Liquid\Blog\Helper;

class ReadingTime
{
    public static function estimateReadingTime(string $text, int $wordPerMinute = 250): int
    {
        $totalWords = str_word_count($text);
        return (int)floor($totalWords / $wordPerMinute);
    }
}
