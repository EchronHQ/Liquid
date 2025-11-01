<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout;

class ScheduledStructure
{
    /**#@+
     * Keys for array of elements to sort
     */
    public const ELEMENT_NAME = 'elementName';
    public const ELEMENT_PARENT_NAME = 'parentName';
    public const ELEMENT_OFFSET_OR_SIBLING = 'offsetOrSibling';
    public const ELEMENT_IS_AFTER = 'isAfter';
    /**#@-*/
    /**
     * Information about structural elements, scheduled for creation
     *
     * @var array
     */
    protected array $scheduledStructure = [];
    /**
     * Scheduled structure data
     *
     * @var array
     */
    protected array $scheduledData = [];
    /**
     * Full information about elements to be populated in the layout structure after generating structure
     *
     * @var array
     */
    protected array $scheduledElements = [];
    /**
     * Scheduled structure elements moves
     *
     * @var array
     */
    protected array $scheduledMoves = [];
    /**
     * Scheduled structure elements removes
     *
     * @var array
     */
    protected array $scheduledRemoves = [];
    /**
     * Materialized paths for overlapping workaround of scheduled structural elements
     *
     * @var array
     */
    protected array $scheduledPaths = [];
    /**
     * Elements with reference to non-existing parent element
     *
     * @var array
     */
    protected array $brokenParent = [];
    /**
     * Elements that need to sort
     *
     * @var array
     */
    protected array $elementsToSort = [];
    /**#@-*/
    private array $serializableProperties = [
        'scheduledStructure',
        'scheduledData',
        'scheduledElements',
        'scheduledMoves',
        'scheduledRemoves',
        'scheduledPaths',
        'elementsToSort',
        'brokenParent',
    ];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->populateWithArray($data);
    }

    /**
     * Update 'Layout scheduled structure' data.
     *
     * @param array $data
     * @return void
     * @since 101.0.0
     */
    public function populateWithArray(array $data): void
    {
        foreach ($this->serializableProperties as $property) {
            $this->{$property} = $this->getArrayValueByKey($property, $data);
        }
    }

    /**
     * Get value from array by key.
     *
     * @param string $key
     * @param array $array
     * @return array
     */
    private function getArrayValueByKey($key, array $array)
    {
        return $array[$key] ?? [];
    }

    /**
     * Set elements to sort
     *
     * @param string $parentName
     * @param string $elementName
     * @param string|int|null $offsetOrSibling
     * @param bool $isAfter
     * @return void
     */
    public function setElementToSortList($parentName, $elementName, $offsetOrSibling, $isAfter = true)
    {
        $this->elementsToSort[$elementName] = [
            self::ELEMENT_NAME => $elementName,
            self::ELEMENT_PARENT_NAME => $parentName,
            self::ELEMENT_OFFSET_OR_SIBLING => $offsetOrSibling,
            self::ELEMENT_IS_AFTER => $isAfter,
        ];
    }

    /**
     * Check if elements list of sorting is empty
     *
     * @return bool
     */
    public function isListToSortEmpty()
    {
        return empty($this->elementsToSort);
    }

    /**
     * Unset specified element from list of sorting
     *
     * @param string $elementName
     * @return void
     */
    public function unsetElementToSort($elementName)
    {
        unset($this->elementsToSort[$elementName]);
    }

    /**
     * Get element to sort by name
     *
     * @param string $elementName
     * @param array $default
     * @return array
     */
    public function getElementToSort($elementName, array $default = [])
    {
        return $this->elementsToSort[$elementName] ?? $default;
    }

    /**
     * Get elements to sort
     *
     * @return array
     */
    public function getListToSort()
    {
        return $this->elementsToSort;
    }

    /**
     * Get elements to move
     *
     * @return array
     */
    public function getListToMove()
    {
        return \array_keys(\array_intersect_key($this->scheduledElements, $this->scheduledMoves));
    }

    /**
     * Get elements to remove
     *
     * @return array
     */
    public function getListToRemove()
    {
        return \array_keys(\array_intersect_key(
            $this->scheduledElements,
            \array_merge($this->scheduledRemoves, $this->brokenParent)
        ));
    }

    /**
     * Get scheduled elements list
     *
     * @return array
     */
    public function getElements()
    {
        return $this->scheduledElements;
    }

    /**
     * Get element by name
     *
     * @param string $elementName
     * @param array $default
     * @return bool|array
     */
    public function getElement($elementName, $default = [])
    {
        return $this->hasElement($elementName) ? $this->scheduledElements[$elementName] : $default;
    }

    /**
     * Check if element present in scheduled elements list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasElement($elementName)
    {
        return isset($this->scheduledElements[$elementName]);
    }

    /**
     * Check if scheduled elements list is empty
     *
     * @return bool
     */
    public function isElementsEmpty()
    {
        return empty($this->scheduledElements);
    }

    /**
     * Add element to scheduled elements list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setElement($elementName, array $data)
    {
        $this->scheduledElements[$elementName] = $data;
    }

    /**
     * Unset specified element from scheduled elements list
     *
     * @param string $elementName
     * @return void
     */
    public function unsetElement($elementName)
    {
        unset($this->scheduledElements[$elementName]);
    }

    /**
     * Get element to move by name
     *
     * @param string $elementName
     * @param mixed $default
     * @return mixed
     */
    public function getElementToMove($elementName, $default = null)
    {
        return $this->scheduledMoves[$elementName] ?? $default;
    }

    /**
     * Add element to move list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setElementToMove($elementName, array $data)
    {
        $this->scheduledMoves[$elementName] = $data;
    }

    /**
     * Unset removed element by name
     *
     * @param string $elementName
     * @return void
     */
    public function unsetElementFromListToRemove($elementName)
    {
        unset($this->scheduledRemoves[$elementName]);
    }

    /**
     * Set removed element value
     *
     * @param string $elementName
     * @return void
     */
    public function setElementToRemoveList($elementName)
    {
        $this->scheduledRemoves[$elementName] = 1;
    }

    /**
     * Get scheduled structure
     *
     * @return array
     */
    public function getStructure()
    {
        return $this->scheduledStructure;
    }

    /**
     * Get element of scheduled structure
     *
     * @param string $elementName
     * @param mixed|null $default
     * @return mixed
     */
    public function getStructureElement($elementName, $default = null)
    {
        return $this->hasStructureElement($elementName) ? $this->scheduledStructure[$elementName] : $default;
    }

    /**
     * Check if element present in scheduled structure elements list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasStructureElement($elementName)
    {
        return isset($this->scheduledStructure[$elementName]);
    }

    /**
     * Check if scheduled structure is empty
     *
     * @return bool
     */
    public function isStructureEmpty()
    {
        return empty($this->scheduledStructure);
    }

    /**
     * Add element to scheduled structure elements list
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setStructureElement($elementName, array $data)
    {
        $this->scheduledStructure[$elementName] = $data;
    }

    /**
     * Unset scheduled structure element by name
     *
     * @param string $elementName
     * @return void
     */
    public function unsetStructureElement($elementName)
    {
        unset($this->scheduledStructure[$elementName]);
        unset($this->scheduledData[$elementName]);
    }

    /**
     * Get scheduled data for element
     *
     * @param string $elementName
     * @param null $default
     * @return null
     */
    public function getStructureElementData($elementName, $default = null)
    {
        return $this->scheduledData[$elementName] ?? $default;
    }

    /**
     * Set scheduled data for element
     *
     * @param string $elementName
     * @param array $data
     * @return void
     */
    public function setStructureElementData($elementName, array $data)
    {
        $this->scheduledData[$elementName] = $data;
    }

    /**
     * Get scheduled paths
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->scheduledPaths;
    }

    /**
     * Get path from paths list
     *
     * @param string $elementName
     * @param mixed $default
     * @return mixed
     */
    public function getPath($elementName, $default = null)
    {
        return $this->hasPath($elementName) ? $this->scheduledPaths[$elementName] : $default;
    }

    /**
     * Check if element present in scheduled paths list
     *
     * @param string $elementName
     * @return bool
     */
    public function hasPath($elementName)
    {
        return isset($this->scheduledPaths[$elementName]);
    }

    /**
     * Add element to scheduled paths elements list
     *
     * @param string $elementName
     * @param string $data
     * @return void
     */
    public function setPathElement($elementName, $data)
    {
        $this->scheduledPaths[$elementName] = $data;
    }

    /**
     * Unset scheduled paths element by name
     *
     * @param string $elementName
     * @return void
     */
    public function unsetPathElement($elementName)
    {
        unset($this->scheduledPaths[$elementName]);
    }

    /**
     * Remove element from broken parent list
     *
     * @param string $elementName
     * @return void
     */
    public function unsetElementFromBrokenParentList($elementName)
    {
        unset($this->brokenParent[$elementName]);
    }

    /**
     * Set element to broken parent list
     *
     * @param string $elementName
     * @return void
     */
    public function setElementToBrokenParentList($elementName)
    {
        $this->brokenParent[$elementName] = 1;
    }

    /**
     * Flush scheduled structure list
     *
     * @return void
     */
    public function flushScheduledStructure()
    {
        $this->flushPaths();
        $this->scheduledElements = [];
        $this->scheduledStructure = [];
    }

    /**
     * Flush scheduled paths list
     *
     * @return void
     */
    public function flushPaths()
    {
        $this->scheduledPaths = [];
    }

    /**
     * Reformat 'Layout scheduled structure' to array.
     *
     * @return array
     * @since 101.0.0
     */
    public function __toArray()
    {
        $result = [];
        foreach ($this->serializableProperties as $property) {
            $result[$property] = $this->{$property};
        }

        return $result;
    }
}
