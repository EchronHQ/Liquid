<?php
declare(strict_types=1);

namespace Liquid\Framework\Data;

use Liquid\Framework\Exception\RuntimeException;

class DataStructure
{
    private const CHILDREN = 'children';
    private const PARENT = 'parent';
    private const GROUPS = 'groups';

    private array $elements = [];
    private array $nameIncrement = [];

    public function __construct(
        array|null $elements = null
    )
    {
        if ($elements !== null) {
            $this->importElements($elements);
        }

    }

    /**
     * Set elements from external source
     *
     * @param array $elements
     * @return void
     * @throws RuntimeException if any format issues identified
     */
    public function importElements(array $elements): void
    {
        $this->elements = $elements;
        foreach ($elements as $elementId => $element) {
            if (\is_numeric($elementId)) {
                throw new RuntimeException(
                    "Element ID must not be numeric: '" . $elementId . "'.",
                );
            }
            $this->_assertParentRelation($elementId);
            if (isset($element[self::GROUPS])) {
                $groups = $element[self::GROUPS];
                $this->_assertArray($groups);
                foreach ($groups as $groupName => $group) {
                    $this->_assertArray($group);
                    if ($group !== \array_flip($group)) {
                        throw new RuntimeException(

                            '"' . \var_export($group, true) . '" is an invalid format of "' . $groupName . '" group. Verify the format and try again.'


                        );
                    }
                    foreach ($group as $groupElementId) {
                        $this->_assertElementExists($groupElementId);
                    }
                }
            }
        }
    }

    /**
     * Verify relations of parent-child
     *
     * @param string $elementId
     * @return void
     * @throws RuntimeException
     */
    protected function _assertParentRelation(string $elementId): void
    {
        $element = $this->elements[$elementId];

        // element presence in its parent's nested set
        if (isset($element[self::PARENT])) {
            $parentId = $element[self::PARENT];
            $this->_assertElementExists($parentId);
            if (empty($this->elements[$parentId][self::CHILDREN][$elementId])) {
                throw new RuntimeException(
                    'The "' . $elementId . '" is not in the nested set of "' . $parentId . '", causing the parent-child relation to break. '
                    . 'Verify and try again.'

                );
            }
        }

        // element presence in its children
        if (isset($element[self::CHILDREN])) {
            $children = $element[self::CHILDREN];
            $this->_assertArray($children);
            if ($children !== \array_flip(\array_flip($children))) {
                throw new RuntimeException(

                    'The "' . \var_export($children, true) . '" format of children is invalid. Verify and try again.');
            }
            foreach (\array_keys($children) as $childId) {
                $this->_assertElementExists($childId);
                if (!isset(
                        $this->elements[$childId][self::PARENT]
                    ) || $elementId !== $this->elements[$childId][self::PARENT]
                ) {
                    throw new RuntimeException(
                        'The "' . $childId . '" doesn\'t have "' . $elementId . '" as parent, causing the parent-child relation to break. '
                        . 'Verify and try again.'

                    );
                }
            }
        }
    }

    private function _assertElementExists(string $elementId): void
    {
        if (!isset($this->elements[$elementId])) {
            throw new \OutOfBoundsException(
                'The element with the "' . $elementId . '" ID wasn\'t found.'
            );
        }
    }

    /**
     * Check if it is an array
     *
     * @param array $value
     * @return void
     * @throws RuntimeException
     */
    private function _assertArray(mixed $value): void
    {
        if (!\is_array($value)) {
            throw new RuntimeException("An array expected: " . \var_export($value, true));
        }
    }

    public function getElement(string $elementId): array|null
    {
        return $this->elements[$elementId] ?? null;
    }

    public function renameElement(string $oldId, string $newId): void
    {
        $this->_assertElementExists($oldId);
        if (!$newId || isset($this->elements[$newId])) {
            throw new \Exception('An element with id "' . $newId . '" is already defined.');
        }

        // rename in registry
        $this->elements[$newId] = $this->elements[$oldId];

        // rename references in children
        if (isset($this->elements[$oldId][self::CHILDREN])) {
            foreach (\array_keys($this->elements[$oldId][self::CHILDREN]) as $childId) {
                $this->_assertElementExists($childId);
                $this->elements[$childId][self::PARENT] = $newId;
            }
        }

        // rename key in its parent's children array
        if (isset($this->elements[$oldId][self::PARENT]) && ($parentId = $this->elements[$oldId][self::PARENT])) {
            $alias = $this->elements[$parentId][self::CHILDREN][$oldId];
            $offset = $this->_getChildOffset($parentId, $oldId);
            unset($this->elements[$parentId][self::CHILDREN][$oldId]);
            $this->setAsChild($newId, $parentId, $alias, $offset);
        }

        unset($this->elements[$oldId]);

    }

    protected function _getChildOffset(string $parentId, string $childId): int
    {
        $index = \array_search($childId, \array_keys($this->getChildren($parentId)), true);
        if ($index === false) {
            throw new \Exception('The "' . $childId . '" is not a child of "' . $parentId . '".');
        }
        return $index;
    }

    public function getChildren(string $parentId): array
    {
        return $this->elements[$parentId][self::CHILDREN] ?? [];
    }

    public function setAsChild(string $elementId, string $parentId, string $alias = '', int|null $position = null): void
    {
        if ($elementId === $parentId) {
            throw new \Exception('The "' . $elementId . '" was incorrectly set as a child to itself.');
        }
        if ($this->_isParentRecursively($elementId, $parentId)) {
            throw new \Exception('The "' . $elementId . '" cannot be set as child to "' . $elementId . '" because "' . $elementId . '" is a parent of "' . $parentId . '" recursively.');
        }
        $this->unsetChild($elementId);
        unset($this->elements[$parentId][self::CHILDREN][$elementId]);
        $this->_insertChild($parentId, $elementId, $position, $alias);
    }

    private function _isParentRecursively(string $childId, string $potentialParentId): bool
    {
        $parentId = $this->getParentId($potentialParentId);
        if (\is_null($parentId)) {
            return false;
        }
        if ($parentId === $childId) {
            return true;
        }
        return $this->_isParentRecursively($childId, $parentId);
    }

    public function getParentId(string $childId): string|null
    {
        return $this->elements[$childId][self::PARENT] ?? null;
    }

    public function unsetChild(string $elementId, string|null $alias = null): self
    {
        if (\is_null($alias)) {
            $childId = $elementId;
        } else {
            $childId = $this->getChildId($elementId, $alias);
        }
        $parentId = $this->getParentId($childId);
        unset($this->elements[$childId][self::PARENT]);
        if (!\is_null($parentId)) {
            unset($this->elements[$parentId][self::CHILDREN][$childId]);
            if (empty($this->elements[$parentId][self::CHILDREN])) {
                unset($this->elements[$parentId][self::CHILDREN]);
            }
        }
        return $this;
    }

    public function getChildId(string $parentId, string $alias): string|null
    {
        if (isset($this->elements[$parentId][self::CHILDREN])) {

            $index = \array_search($alias, $this->elements[$parentId][self::CHILDREN], true);
            if ($index !== false) {
                return $index;
            }
        }
        return null;
    }

    protected function _insertChild(string $targetParentId, string $elementId, int|null $offset, string $alias): void
    {
        $alias = $alias ?: $elementId;

        // validate
        $this->_assertElementExists($elementId);
        if (!empty($this->elements[$elementId][self::PARENT])) {
            throw new \Exception('The element "' . $elementId . '" can\'t have a parent because "' . $this->elements[$elementId][self::PARENT] . '" is already the parent of "' . $elementId . '".');
        }
        $this->_assertElementExists($targetParentId);
        $children = $this->getChildren($targetParentId);
        if (isset($children[$elementId])) {
            throw new \Exception('The element "' . $elementId . '" is already a child of "' . $targetParentId . '".');
        }
        if (\in_array($alias, $children, true)) {
            throw new \Exception('The element "' . $targetParentId . '" can\'t have a child because "' . $targetParentId . '" already has a child with alias "' . $alias . '".');
        }

        // insert
        if (\is_null($offset)) {
            $offset = count($children);
        }
        $this->elements[$targetParentId][self::CHILDREN] = \array_merge(
            \array_slice($children, 0, $offset),
            [$elementId => $alias],
            \array_slice($children, $offset)
        );
        $this->elements[$elementId][self::PARENT] = $targetParentId;
    }

    public function createStructuralElement(string $name, string $type, string $class): string
    {
        if (empty($name)) {
            $name = $this->_generateAnonymousName($class);
        }
        $this->createElement($name, ['type' => $type]);
        return $name;
    }

    protected function _generateAnonymousName(string $class): string
    {
        $position = \strpos($class, '\\Block\\');
        $key = $position !== false ? \substr($class, $position + 7) : $class;
        $key = \strtolower(\trim($key, '_'));

        if (!isset($this->nameIncrement[$key])) {
            $this->nameIncrement[$key] = 0;
        }
        do {
            $name = $key . '_' . $this->nameIncrement[$key]++;
        } while ($this->hasElement($name));

        return $name;
    }

    public function hasElement(string $elementId): bool
    {
        return isset($this->elements[$elementId]);
    }

    public function createElement(string $elementId, array $data): void
    {
        if (isset($this->elements[$elementId])) {
            throw new \Exception('An element with id "' . $elementId . '" already exists.');

        }
        $this->elements[$elementId] = [];

        foreach ($data as $key => $value) {
            $this->setAttribute($elementId, $key, $value);
        }
    }

    public function setAttribute(string $elementId, string $attribute, mixed $value): void
    {
        $this->_assertElementExists($elementId);
        switch ($attribute) {
            case self::PARENT:
                // break is intentionally omitted
            case self::CHILDREN:
                //            case self::GROUPS:
                //                throw new \InvalidArgumentException("The '{$attribute}' attribute is reserved and can't be set.");
            default:
                $this->elements[$elementId][$attribute] = $value;
        }

    }

    /**
     * Remove element with specified ID from the structure
     *
     * Can recursively delete all child elements.
     * Returns false if there was no element found, therefore was nothing to delete.
     *
     * @param string $elementId
     * @param bool $recursive
     * @return bool
     */
    public function unsetElement(string $elementId, bool $recursive = true): bool
    {
        if (isset($this->elements[$elementId][self::CHILDREN])) {
            foreach (\array_keys($this->elements[$elementId][self::CHILDREN]) as $childId) {
                $this->_assertElementExists($childId);
                if ($recursive) {
                    $this->unsetElement($childId, $recursive);
                } else {
                    unset($this->elements[$childId][self::PARENT]);
                }
            }
        }
        $this->unsetChild($elementId);
        $wasFound = isset($this->elements[$elementId]);
        unset($this->elements[$elementId]);
        return $wasFound;
    }

    public function getAttribute(string $elementId, string $attribute): mixed
    {
        $this->_assertElementExists($elementId);
        return $this->elements[$elementId][$attribute] ?? false;
    }

    public function exportElements(): array
    {
        return $this->elements;
    }

    /**
     * Reorder an element relatively to its sibling
     *
     * $offset possible values:
     *    1,  2 -- set after the sibling towards end -- by 1, by 2 positions, etc
     *   -1, -2 -- set before the sibling towards start -- by 1, by 2 positions, etc...
     *
     * Both $childId and $siblingId must be children of the specified $parentId
     * Returns new position of the reordered element
     *
     * @param string $parentId
     * @param string $childId
     * @param string $siblingId
     * @param int $offset
     * @return int
     */
    public function reorderToSibling(string $parentId, string $childId, string $siblingId, int $offset): int
    {
        $this->_getChildOffset($parentId, $childId);
        if ($childId === $siblingId) {
            $newOffset = $this->_getRelativeOffset($parentId, $siblingId, $offset);
            return $this->reorderChild($parentId, $childId, $newOffset);
        }
        $alias = $this->getChildAlias($parentId, $childId);
        $newOffset = $this->unsetChild($childId)->_getRelativeOffset($parentId, $siblingId, $offset);
        $this->_insertChild($parentId, $childId, $newOffset, $alias);
        return $this->_getChildOffset($parentId, $childId) + 1;
    }

    /**
     * Calculate new offset for placing an element relatively specified sibling under the same parent
     *
     * @param string $parentId
     * @param string $siblingId
     * @param int $delta
     * @return int
     */
    private function _getRelativeOffset(string $parentId, string $siblingId, int $delta): int
    {
        $newOffset = $this->_getChildOffset($parentId, $siblingId) + $delta;
        if ($delta < 0) {
            ++$newOffset;
        }
        if ($newOffset < 0) {
            $newOffset = 0;
        }
        return $newOffset;
    }

    /**
     * Reorder a child element relatively to specified position
     *
     * Returns new position of the reordered element
     *
     * @param string $parentId
     * @param string $childId
     * @param int|null $position
     * @return int
     * @see _insertChild() for position explanation
     */
    public function reorderChild(string $parentId, string $childId, int|null $position): int
    {
        $alias = $this->getChildAlias($parentId, $childId);
        $currentOffset = $this->_getChildOffset($parentId, $childId);
        $offset = $position;
        if ($position > 0) {
            if ($position >= $currentOffset + 1) {
                --$offset;
            }
        } elseif ($position < 0) {
            if ($position < $currentOffset + 1 - count($this->elements[$parentId][self::CHILDREN])) {
                if ($position === -1) {
                    $offset = null;
                } else {
                    ++$offset;
                }
            }
        }
        $this->unsetChild($childId)->_insertChild($parentId, $childId, $offset, $alias);
        return $this->_getChildOffset($parentId, $childId) + 1;
    }

    /**
     * Get element alias by name
     *
     * @param string $parentId
     * @param string $childId
     * @return string|null
     */
    public function getChildAlias(string $parentId, string $childId): string|null
    {
        return $this->elements[$parentId][self::CHILDREN][$childId] ?? null;
    }
}
