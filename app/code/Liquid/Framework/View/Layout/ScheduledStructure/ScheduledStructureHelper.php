<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout\ScheduledStructure;

use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\State;
use Liquid\Framework\View\Layout\Data\LayoutDataStructure;
use Liquid\Framework\View\Layout\LayoutElement;
use Liquid\Framework\View\Layout\ScheduledStructure;
use Psr\Log\LoggerInterface;

class ScheduledStructureHelper
{
    /**
     * Scheduled structure array indexes
     */
    public const SCHEDULED_STRUCTURE_INDEX_TYPE = 0;
    public const SCHEDULED_STRUCTURE_INDEX_ALIAS = 1;
    public const SCHEDULED_STRUCTURE_INDEX_PARENT_NAME = 2;
    public const SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME = 3;
    public const SCHEDULED_STRUCTURE_INDEX_IS_AFTER = 4;

    /**
     * @var int
     */
    protected int $counter = 0;


    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly State $appState
    )
    {
    }

    /**
     * Populate queue for generating structural elements
     *
     * @param ScheduledStructure $scheduledStructure
     * @param LayoutElement $currentNode
     * @param LayoutElement $parentNode
     * @return string
     * @see scheduleElement() where the scheduledStructure is used
     */
    public function scheduleStructure(ScheduledStructure $scheduledStructure,
                                      LayoutElement      $currentNode,
                                      LayoutElement      $parentNode
    ): string
    {
        // if it hasn't a name it must be generated
        if (!(string)$currentNode->getAttribute('name')) {
            $name = $this->_generateAnonymousName($parentNode->getElementName() . '_schedule_block');
            $currentNode->setAttribute('name', $name);
        }
        $path = $name = (string)$currentNode->getAttribute('name');

        // Prepare scheduled element with default parameters [type, alias, parentName, siblingName, isAfter]
        $row = [
            self::SCHEDULED_STRUCTURE_INDEX_TYPE => $currentNode->getName(),
            self::SCHEDULED_STRUCTURE_INDEX_ALIAS => '',
            self::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME => '',
            self::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME => null,
            self::SCHEDULED_STRUCTURE_INDEX_IS_AFTER => true,
        ];

        $parentName = $parentNode->getElementName();
        //if this element has a parent element, there must be reset [alias, parentName, siblingName, isAfter]
        if ($parentName) {
            $row[self::SCHEDULED_STRUCTURE_INDEX_ALIAS] = (string)$currentNode->getAttribute('as');
            $row[self::SCHEDULED_STRUCTURE_INDEX_PARENT_NAME] = $parentName;

            [$row[self::SCHEDULED_STRUCTURE_INDEX_SIBLING_NAME],
                $row[self::SCHEDULED_STRUCTURE_INDEX_IS_AFTER]] = $this->_beforeAfterToSibling($currentNode);

            // materialized path for referencing nodes in the plain array of _scheduledStructure
            if ($scheduledStructure->hasPath($parentName)) {
                $path = $scheduledStructure->getPath($parentName) . '/' . $path;
            }
        }

        $this->_overrideElementWorkaround($scheduledStructure, $name, $path);
        $scheduledStructure->setPathElement($name, $path);
        $scheduledStructure->setStructureElement($name, $row);
        return $name;
    }

    /**
     * Generate anonymous element name for structure
     *
     * @param string $class
     * @return string
     */
    protected function _generateAnonymousName(string $class): string
    {
        $position = strpos($class, '\\Block\\');
        $key = $position !== false ? substr($class, $position + 7) : $class;
        $key = strtolower(trim($key, '_'));
        return $key . $this->counter++;
    }

    /**
     * Analyze "before" and "after" information in the node and return sibling name and whether "after" or "before"
     *
     * @param LayoutElement $node
     * @return array
     */
    protected function _beforeAfterToSibling(LayoutElement $node): array
    {
        $result = [null, true];
        if (isset($node['after'])) {
            $result[0] = (string)$node['after'];
        } elseif (isset($node['before'])) {
            $result[0] = (string)$node['before'];
            $result[1] = false;
        }
        return $result;
    }

    /**
     * Destroy previous element with same name and all its children, if new element overrides it
     *
     * This is a workaround to handle situation, when an element emerges with name of element that already exists.
     * In this case we destroy entire structure of the former element and replace with the new one.
     *
     * @param ScheduledStructure $scheduledStructure
     * @param string $name
     * @param string $path
     * @return void
     */
    protected function _overrideElementWorkaround(ScheduledStructure $scheduledStructure, string $name, string $path): void
    {
        if ($scheduledStructure->hasStructureElement($name)) {
            $scheduledStructure->setStructureElementData($name, []);
            foreach ($scheduledStructure->getPaths() as $potentialChild => $childPath) {
                if (0 === strpos($childPath ?? '', "{$path}/")) {
                    $scheduledStructure->unsetPathElement($potentialChild);
                    $scheduledStructure->unsetStructureElement($potentialChild);
                }
            }
        }
    }

    /**
     * Process queue of structural elements and actually add them to structure, and schedule elements for generation
     *
     * The catch is to populate parents first, if they are not in the structure yet.
     * Since layout updates could come in arbitrary order, a case is possible where an element is declared in reference,
     * while referenced element itself is not declared yet.
     *
     * @param ScheduledStructure $scheduledStructure
     * @param LayoutDataStructure $structure
     * @param string $key in _scheduledStructure represent element name
     * @return void
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function scheduleElement(
        ScheduledStructure  $scheduledStructure,
        LayoutDataStructure $structure,
        string              $key
    ): void
    {
        $row = $scheduledStructure->getStructureElement($key);
        $data = $scheduledStructure->getStructureElementData($key);
        // if we have reference container to not existed element
        if (!isset($row[self::SCHEDULED_STRUCTURE_INDEX_TYPE])) {
            $this->logger->critical("Broken reference: missing declaration of the element '{$key}'.");
            $scheduledStructure->unsetPathElement($key);
            $scheduledStructure->unsetStructureElement($key);
            return;
        }
        [$type, $alias, $parentName, $siblingName, $isAfter] = $row;
        $name = $this->_createStructuralElement($structure, $key, $type, $parentName . $alias);
        if ($parentName) {
            // recursively populate parent first
            if ($scheduledStructure->hasStructureElement($parentName)) {
                $this->scheduleElement($scheduledStructure, $structure, $parentName);
            }
            if ($structure->hasElement($parentName)) {
                try {
                    $structure->setAsChild($name, $parentName, $alias);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            } else {
                $scheduledStructure->setElementToBrokenParentList($key);

                if ($this->appState->getMode() === AppMode::Develop) {
                    $this->logger->info(
                        "Broken reference: the '{$name}' element cannot be added as child to '{$parentName}', " .
                        'because the latter doesn\'t exist'
                    );
                }
            }
        }

        // Move from scheduledStructure to scheduledElement
        $scheduledStructure->unsetStructureElement($key);
        $scheduledStructure->setElement($name, [$type, $data]);

        /**
         * Some elements provide info "after" or "before" which sibling they are supposed to go
         * Add element into list of sorting
         */
        if ($siblingName) {
            $scheduledStructure->setElementToSortList($parentName, $name, $siblingName, $isAfter);
        }
    }

    /**
     * Register an element in structure
     *
     * Will assign an "anonymous" name to the element, if provided with an empty name
     *
     * @param LayoutDataStructure $structure
     * @param string $name
     * @param string $type
     * @param string $class
     * @return string
     */
    protected function _createStructuralElement(LayoutDataStructure $structure, $name, $type, $class)
    {
        if (empty($name)) {
            $name = $this->_generateAnonymousName($class);
        }
        $structure->createElement($name, ['type' => $type]);
        return $name;
    }
}
