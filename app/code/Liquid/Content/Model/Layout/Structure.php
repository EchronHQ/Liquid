<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Layout;

class Structure
{
    private array $elements = [];
    private array $nameIncrement = [];
    private const CHILDREN = 'children';
    private const PARENT = 'parent';

    public function hasElement(string $elementId): bool
    {
        return isset($this->elements[$elementId]);
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
            foreach (array_keys($this->elements[$oldId][self::CHILDREN]) as $childId) {
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

    private function _assertElementExists(string $elementId): void
    {
        if (!isset($this->elements[$elementId])) {
            throw new \OutOfBoundsException(
                'The element with the "' . $elementId . '" ID wasn\'t found.'
            );
        }
    }

    protected function _getChildOffset(string $parentId, string $childId): int
    {
        $index = array_search($childId, array_keys($this->getChildren($parentId)), true);
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

    public function unsetChild(string $elementId, string|null $alias = null): void
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

    }

    public function getChildId(string $parentId, string $alias): string|null
    {
        if (isset($this->elements[$parentId][self::CHILDREN])) {

            $index = array_search($alias, $this->elements[$parentId][self::CHILDREN], true);
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
        if (in_array($alias, $children, true)) {
            throw new \Exception('The element "' . $targetParentId . '" can\'t have a child because "' . $targetParentId . '" already has a child with alias "' . $alias . '".');
        }

        // insert
        if (\is_null($offset)) {
            $offset = count($children);
        }
        $this->elements[$targetParentId][self::CHILDREN] = array_merge(
            array_slice($children, 0, $offset),
            [$elementId => $alias],
            array_slice($children, $offset)
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
        $position = strpos($class, '\\Block\\');
        $key = $position !== false ? substr($class, $position + 7) : $class;
        $key = strtolower(trim($key, '_'));

        if (!isset($this->nameIncrement[$key])) {
            $this->nameIncrement[$key] = 0;
        }
        do {
            $name = $key . '_' . $this->nameIncrement[$key]++;
        } while ($this->hasElement($name));

        return $name;
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

    public function getAttribute(string $elementId, string $attribute): mixed
    {
        $this->_assertElementExists($elementId);
        return $this->elements[$elementId][$attribute] ?? false;
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

    public function exportElements(): array
    {
        return $this->elements;
    }
}
