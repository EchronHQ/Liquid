<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

class HtmlHelper
{
    public static function renderAttributes(array $attributes): string
    {

        $arrOutput = [];
        foreach ($attributes as $attribute => $value) {
            $arrOutput[] = $attribute . '="' . $value . '"';
        }

        return \implode(' ', $arrOutput);
    }

    public static function renderStyle(array $styles): string
    {

        $arrOutput = [];
        foreach ($styles as $property => $value) {
            $arrOutput[] = $property . ': ' . $value . ';';
        }

        return \implode(' ', $arrOutput);
    }

    public static function removeHtml(string $text): string
    {
        $text = \strip_tags($text, ['<style>', '<script>']);

        // Remove styles <style>...</style>
        $start = \strpos($text, '<style');
        while ($start !== false) {
            $end = \strpos($text, '</style>');
            if (!$text) {
                break;
            }
            $diff = $end - $start + \strlen('</style>');
            $substring = \substr($text, $start, $diff);
            $text = \str_replace($substring, '', $text);
            $start = \strpos($text, '<style');
        }

        // Remove styles <script>...</script>
        $start = \strpos($text, '<script');
        while ($start !== false) {
            $end = \strpos($text, '</script>');
            if (!$text) {
                break;
            }
            $diff = $end - $start + \strlen('</script>');
            $substring = \substr($text, $start, $diff);
            $text = \str_replace($substring, '', $text);
            $start = \strpos($text, '<script');
        }

        // Remaining <style> if any.
        $text = \strip_tags($text);

        // Remove all new lines and tabs and use a space instead.
        $text = \str_replace(["\n", "\r", "\t"], ' ', $text);

        // Trim left and right.
        $text = \trim($text);

        // Remove all spaces that have more than one occurrence.
        return \preg_replace('!\s+!', ' ', $text);
    }

}
