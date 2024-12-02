<?php
declare(strict_types=1);

namespace Liquid\Framework\Simplexml;

class XmlElement extends \SimpleXMLElement
{
    public function descend(string $path)
    {
        return $this->xpath($path);
    }

    /**
     * Returns attribute value by attribute name
     *
     * @param string $name
     * @return string|null
     */
    public function getAttribute(string $name): string|null
    {
        $attrs = $this->attributes();
        return isset($attrs[$name]) ? (string)$attrs[$name] : null;
    }

    /**
     * Create attribute if it does not exists and set value to it
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setAttribute(string $name, string $value): void
    {
        if (!isset($this->attributes()[$name])) {
            $this->addAttribute($name, $value);
        }

        $this->attributes()[$name] = $value;
    }

    /**
     * Get children XML
     *
     * @param int $level
     * @return string
     */
    public function innerXml(int $level = 0): string
    {
        $out = '';
        foreach ($this->children() as $child) {
            $out .= $child->asXML();
        }
        return $out;
    }
}
