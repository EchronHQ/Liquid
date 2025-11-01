<?php
declare(strict_types=1);

namespace Liquid\Framework\Simplexml;

class XmlHelper
{
    /**
     * Get formatted xml errors
     *
     * @param array|null $libXmlErrors
     * @return array
     */
    public function getXmlErrors(array|null $libXmlErrors = null): array
    {
        if ($libXmlErrors === null) {
            $libXmlErrors = \libxml_get_errors();
        }
        $errors = [];
        if (count($libXmlErrors)) {
            foreach ($libXmlErrors as $error) {
                $errors[] = "{$error->message} Line: {$error->line}";
            }
        }
        return $errors;
    }

    /**
     * Return attributes of XML node rendered as a string
     *
     * @param \SimpleXMLElement $node
     * @return string
     */
    public function renderXmlAttributes(\SimpleXMLElement $node): string
    {
        $result = '';
        foreach ($node->attributes() as $attributeName => $attributeValue) {
            $result .= ' ' . $attributeName . '="' . $attributeValue . '"';
        }
        return $result;
    }
}
