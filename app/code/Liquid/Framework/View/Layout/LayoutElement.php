<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout;

use Liquid\Framework\Simplexml\XmlElement;

class LayoutElement extends XmlElement
{
    /**
     * Get element name
     *
     * Advanced version of getBlockName() method: gets name for container as well as for block
     *
     * @return string|null
     */
    public function getElementName(): string|null
    {
        $tagName = $this->getName();
//        $isThisContainer = !in_array(
//            $tagName,
//            [self::TYPE_BLOCK, self::TYPE_REFERENCE_BLOCK, self::TYPE_CONTAINER, self::TYPE_REFERENCE_CONTAINER]
//        );

//        if ($isThisContainer) {
//            return false;
//        }
        return $this->getAttribute('name');
    }
}
