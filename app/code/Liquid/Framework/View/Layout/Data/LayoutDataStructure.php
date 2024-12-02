<?php

declare(strict_types=1);

namespace Liquid\Framework\View\Layout\Data;

use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\State;
use Liquid\Framework\Data\DataStructure;
use Psr\Log\LoggerInterface;

class LayoutDataStructure extends DataStructure
{


    public function __construct(
        private readonly State           $appState,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct();
    }

    /**
     * Reorder a child of a specified element
     *
     * If $offsetOrSibling is null, it will put the element to the end
     * If $offsetOrSibling is numeric (integer) value, it will put the element after/before specified position
     * Otherwise -- after/before specified sibling
     *
     * @param string $parentName
     * @param string $childName
     * @param string|int|null $offsetOrSibling
     * @param bool $after
     * @return void
     */
    public function reorderChildElement(string $parentName, string $childName, int|null $offsetOrSibling, bool $after = true): void
    {
        if (is_numeric($offsetOrSibling)) {
            $offset = abs((int)$offsetOrSibling) * ($after ? 1 : -1);
            $this->reorderChild($parentName, $childName, $offset);
        } elseif (null === $offsetOrSibling) {
            $this->reorderChild($parentName, $childName, null);
        } else {
            $children = array_keys($this->getChildren($parentName));
            if ($this->getChildId($parentName, $offsetOrSibling) !== false) {
                $offsetOrSibling = $this->getChildId($parentName, $offsetOrSibling);
            }
            $sibling = $this->_filterSearchMinus($offsetOrSibling, $children, $after);
            if ($childName !== $sibling) {
                $siblingParentName = $this->getParentId($sibling);
                if ($parentName !== $siblingParentName) {
                    if ($this->appState->getMode() === AppMode::Develop) {
                        $this->logger->info(
                            "Broken reference: the '{$childName}' tries to reorder itself towards '{$sibling}', but " .
                            "their parents are different: '{$parentName}' and '{$siblingParentName}' respectively."
                        );
                    }
                    return;
                }
                $this->reorderToSibling($parentName, $childName, $sibling, $after ? 1 : -1);
            }
        }
    }

    /**
     * Search for an array element using needle, but needle may be '-', which means "first" or "last" element
     *
     * Returns first or last element in the haystack, or the $needle argument
     *
     * @param string $needle
     * @param array $haystack
     * @param bool $isLast
     * @return string
     */
    protected function _filterSearchMinus(string $needle, array $haystack, bool $isLast): string
    {
        if ('-' === $needle) {
            if ($isLast) {
                return array_pop($haystack);
            }
            return array_shift($haystack);
        }
        return $needle;
    }
}
