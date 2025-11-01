<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

class StringHelper
{
    /**
     * Combines words to a sentence: [wordA], [wordB] and [wordC]
     * @param array $values
     * @return string
     */
    public static function concatListOfWords(array $values): string
    {
        $strCategories = '';

        $c = count($values);
        foreach ($values as $i => $value) {

            $strCategories .= $value;

            if ($i === $c - 2) {
                $strCategories .= ' and ';
            } elseif ($i < $c - 2) {
                $strCategories .= ', ';
            }
        }

        return $strCategories;
    }

    public static function formatDate(\DateTime $dateTime): string
    {
        return $dateTime->format('M j, Y');
    }

    // TODO: unit test this
    public static function getOccurrences(string $html, string $needle): array
    {

        $positions = [];

        // Make sure we match the needle as a full word, not part of a word.
        // TODO: seems like this should be a regex
        $finds = [
            ' ' . $needle . ' ',
            ' ' . $needle . ',',
            ' ' . $needle . '.',
            ',' . $needle . '.',
            ',' . $needle . ',',
        ];
        foreach ($finds as $query) {
            $lastPos = 0;
            while (($lastPos = \stripos($html, $query, $lastPos)) !== false) {
                $positions[] = $lastPos;
                $lastPos += \strlen($needle);
            }
        }


        return $positions;
    }

    public static function mask(string $input): string
    {
        return \implode('&shy;', \str_split($input));
    }

    public static function startsWith(string $needle, array $haystacks): bool
    {
        foreach ($haystacks as $haystack) {
            if (str_starts_with($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }
}
