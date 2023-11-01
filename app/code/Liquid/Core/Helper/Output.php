<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

class Output
{
    public function escapeHtmlAttribute(string $input, bool $stripHtml = true): string
    {
        if ($stripHtml) {
            $input = strip_tags($input);
        }
        $input = $this->escapeTerms($input);
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }

    private function escapeTerms(string $input): string
    {
        preg_match_all('/\{TERM}(.*?)\{\/TERM}/s', $input, $matches);

        [$toReplace, $foundTerms] = $matches;

        $buildTerms = [];
        foreach ($foundTerms as $foundTerm) {
            $buildTerms[] = $foundTerm;
        }
        return \str_replace($toReplace, $buildTerms, $input);

    }

    public function jsonEncode(mixed $input): string
    {
        return json_encode($input, JSON_THROW_ON_ERROR);
    }
}
