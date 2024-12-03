<?php
declare(strict_types=1);

namespace Liquid\Framework\Simplexml;

class XmlConfig
{
    private XmlElement $xml;

    /**
     * Returns node found by the $path
     *
     * @param string|null $path
     * @return XmlElement|bool
     * @see \Liquid\Framework\Simplexml\XmlElement::descend
     */
    public function getNode(string|null $path = null): XmlElement|bool
    {
        if (!$this->getXml() instanceof XmlElement) {
            return false;
        }

        if ($path === null) {
            return $this->getXml();
        }

        return $this->getXml()->descend($path);
    }

    /**
     * Getter for xml element
     *
     * @return XmlElement|null
     */
    protected function getXml(): XmlElement|null
    {
        return $this->xml;
    }

    /**
     * Sets xml for this configuration
     *
     * @param XmlElement $node
     * @return $this
     */
    public function setXml(XmlElement $node): self
    {
        $this->xml = $node;
        return $this;
    }
}
