<?php
declare(strict_types=1);

namespace Liquid\Framework;

use Psr\Log\LoggerInterface;

class Escaper
{
    private static string $xssFiltrationPattern =
        '/((javascript(\\\\x3a|:|%3A))|(data(\\\\x3a|:|%3A))|(vbscript:))|'
        . '((\\\\x6A\\\\x61\\\\x76\\\\x61\\\\x73\\\\x63\\\\x72\\\\x69\\\\x70\\\\x74(\\\\x3a|:|%3A))|'
        . '(\\\\x64\\\\x61\\\\x74\\\\x61(\\\\x3a|:|%3A)))/i';
    private array $notAllowedTags = ['script', 'img', 'embed', 'iframe', 'video', 'source', 'object', 'audio'];
    private array $notAllowedAttributes = ['a' => ['style']];
    private array $allowedAttributes = ['id', 'class', 'href', 'title', 'style'];
    private array $escapeAsUrlAttributes = ['href'];
    private int $htmlSpecialCharsFlag = ENT_QUOTES | ENT_SUBSTITUTE;

    public function __construct(
        private readonly LoggerInterface $logger,
    )
    {
    }

    public function escapeHtmlAttribute(string $input, bool $stripHtml = true): string
    {
        if ($stripHtml) {
            $input = \strip_tags($input);
        }
        $input = $this->escapeTerms($input);
        return \htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }

    public function jsonEncode(mixed $input): string
    {
        return \json_encode($input, JSON_THROW_ON_ERROR);
    }

    /**
     * Escape string for HTML context.
     *
     * AllowedTags will not be escaped, except the following: script, img, embed,
     * iframe, video, source, object, audio
     *
     * @param string $data
     * @param array|null $allowedTags
     * @return string
     */
    public function escapeHtml(string $data, array|null $allowedTags = null): string
    {


        if (!empty($data)) {
            if (\is_array($allowedTags) && !empty($allowedTags)) {
                $allowedTags = $this->filterProhibitedTags($allowedTags);
                $wrapperElementId = \uniqid('', true);
                $domDocument = new \DOMDocument('1.0', 'UTF-8');
                \set_error_handler(
                    static function ($errorNumber, $errorString) {
                        throw new \InvalidArgumentException($errorString, $errorNumber);
                    }
                );
                $data = $this->prepareUnescapedCharacters($data);
                $convmap = [0x80, 0x10FFFF, 0, 0x1FFFFF];
                $string = mb_encode_numericentity(
                    $data,
                    $convmap,
                    'UTF-8'
                );
                try {
                    $domDocument->loadHTML(
                        '<html><body id="' . $wrapperElementId . '">' . $string . '</body></html>'
                    );
                } catch (\Exception $e) {
                    \restore_error_handler();
                    $this->logger->critical($e);
                }
                \restore_error_handler();

                $this->removeComments($domDocument);
                $this->removeNotAllowedTags($domDocument, $allowedTags);
                $this->removeNotAllowedAttributes($domDocument);
                $this->escapeText($domDocument);
                $this->escapeAttributeValues($domDocument);

                $result = mb_decode_numericentity(
                    \html_entity_decode(
                        $domDocument->saveHTML(),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    ),
                    $convmap,
                    'UTF-8'
                );

                \preg_match('/<body id="' . $wrapperElementId . '">(.+)<\/body><\/html>$/si', $result, $matches);
                return !empty($matches) ? $matches[1] : '';
            }

            $result = \htmlspecialchars($data, $this->htmlSpecialCharsFlag, 'UTF-8', false);
        } else {
            $result = $data;
        }
        return $result;
    }

    /**
     * Escape URL
     *
     * @param string $string
     * @return string
     */
    public function escapeUrl(string $string): string
    {
        return $this->escapeHtml($this->escapeXssInUrl($string));
    }

    public function encodeUrlParam(string $string): string
    {
        return $this->escapeUrl($string);
    }

    /**
     * Escape xss in urls
     *
     * @param string $data
     * @return string
     * @deprecated 101.0.0
     * @see MAGETWO-54971
     */
    public function escapeXssInUrl(string $data): string
    {
        $data = \html_entity_decode((string)$data);
        // $this->getTranslateInline()->processResponseBody($data);

        return \htmlspecialchars(
            $this->escapeScriptIdentifiers($data),
            $this->htmlSpecialCharsFlag | ENT_HTML5 | ENT_HTML401,
            'UTF-8',
            false
        );
    }

    /**
     * Remove `javascript:`, `vbscript:`, `data:` words from the string.
     *
     * @param string $data
     * @return string
     */
    private function escapeScriptIdentifiers(string $data): string
    {
        $filteredData = \preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $data);
        if ($filteredData === false || $filteredData === '') {
            return '';
        }

        $filteredData = \preg_replace(self::$xssFiltrationPattern, ':', $filteredData);
        if ($filteredData === false) {
            return '';
        }

        if (\preg_match(self::$xssFiltrationPattern, $filteredData)) {
            $filteredData = $this->escapeScriptIdentifiers($filteredData);
        }

        return $filteredData;
    }

    /**
     * Escape text
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function escapeText(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        /** @var \DOMNode[] $nodes */
        $nodes = $xpath->query('//text()');
        foreach ($nodes as $node) {
            $node->textContent = $this->escapeHtml($node->textContent);
        }
    }

    /**
     * Escape attribute values
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function escapeAttributeValues(\DOMDocument $domDocument): void
    {
        $xpath = new \DOMXPath($domDocument);
        /** @var \DOMNode[] $nodes */
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            $value = $this->escapeAttributeValue(
                $node->nodeName,
                $node->parentNode->getAttribute($node->nodeName)
            );
            $node->parentNode->setAttribute($node->nodeName, $value);
        }
    }

    /**
     * Escape attribute value using escapeHtml or escapeUrl
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    private function escapeAttributeValue(string $name, string $value): string
    {
        return \in_array($name, $this->escapeAsUrlAttributes, true) ? $this->escapeUrl($value) : $this->escapeHtml($value);
    }

    /**
     * Remove not allowed tags
     *
     * @param \DOMDocument $domDocument
     * @param string[] $allowedTags
     * @return void
     */
    private function removeNotAllowedTags(\DOMDocument $domDocument, array $allowedTags)
    {
        $xpath = new \DOMXPath($domDocument);
        /** @var \DOMNode[] $nodes */
        $nodes = $xpath->query(
            '//node()[name() != \''
            . \implode('\' and name() != \'', \array_merge($allowedTags, ['html', 'body']))
            . '\']'
        );
        foreach ($nodes as $node) {
            if ($node->nodeName !== '#text') {
                $node->parentNode->replaceChild($domDocument->createTextNode($node->textContent), $node);
            }
        }
    }

    /**
     * Remove comments
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function removeComments(\DOMDocument $domDocument): void
    {
        $xpath = new \DOMXPath($domDocument);
        /** @var \DOMNode[] $nodes */
        $nodes = $xpath->query('//comment()');
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    /**
     * Used to replace characters, that mb_convert_encoding will not process
     *
     * @param string $data
     * @return string|null
     */
    private function prepareUnescapedCharacters(string $data): string|null
    {
        $patterns = ['/\&/u'];
        $replacements = ['&amp;'];
        return \preg_replace($patterns, $replacements, $data);
    }

    /**
     * Filter prohibited tags.
     *
     * @param string[] $allowedTags
     * @return string[]
     */
    private function filterProhibitedTags(array $allowedTags): array
    {
        $notAllowedTags = \array_intersect(
            \array_map('strtolower', $allowedTags),
            $this->notAllowedTags
        );

        if (!empty($notAllowedTags)) {
            $this->logger->critical(
                'The following tag(s) are not allowed: ' . \implode(', ', $notAllowedTags)
            );
            $allowedTags = \array_diff($allowedTags, $this->notAllowedTags);
        }

        return $allowedTags;
    }

    /**
     * Remove not allowed attributes
     *
     * @param \DOMDocument $domDocument
     * @return void
     */
    private function removeNotAllowedAttributes(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        /** @var \DOMNode[] $nodes */
        $nodes = $xpath->query(
            '//@*[name() != \'' . \implode('\' and name() != \'', $this->allowedAttributes) . '\']'
        );
        foreach ($nodes as $node) {
            $node->parentNode->removeAttribute($node->nodeName);
        }

        foreach ($this->notAllowedAttributes as $tag => $attributes) {
            /** @var \DOMNode[] $nodes */
            $nodes = $xpath->query(
                '//@*[name() =\'' . \implode('\' or name() = \'', $attributes) . '\']'
                . '[parent::node()[name() = \'' . $tag . '\']]'
            );
            foreach ($nodes as $node) {
                $node->parentNode->removeAttribute($node->nodeName);
            }
        }
    }

    private function escapeTerms(string $input): string
    {
        \preg_match_all('/\{TERM}(.*?)\{\/TERM}/s', $input, $matches);

        [$toReplace, $foundTerms] = $matches;

        $buildTerms = [];
        foreach ($foundTerms as $foundTerm) {
            $buildTerms[] = $foundTerm;
        }
        return \str_replace($toReplace, $buildTerms, $input);

    }
}
