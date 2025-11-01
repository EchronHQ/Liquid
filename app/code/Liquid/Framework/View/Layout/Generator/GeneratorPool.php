<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout\Generator;

use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\State;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Layout\Data\LayoutDataStructure;
use Liquid\Framework\View\Layout\Reader\Context;
use Liquid\Framework\View\Layout\ScheduledStructure;
use Liquid\Framework\View\Layout\ScheduledStructure\ScheduledStructureHelper;
use Psr\Log\LoggerInterface;

class GeneratorPool
{

    /**
     * @var GeneratorInterface[]
     */
    protected array $generators = [];


    /**
     * @var State
     */
    private State $appState;

    /**
     * @param ScheduledStructureHelper $helper
     * @param LoggerInterface $logger
     * @param array|null $generators
     * @param State|null $state
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        private readonly ScheduledStructure\ScheduledStructureHelper $helper,
        private readonly LoggerInterface                             $logger,
        ObjectManagerInterface                                       $objectManager,
        array|null                                                   $generators = [],
        State|null                                                   $state = null,

    )
    {
        $this->addGenerators($generators);
        $this->appState = $state ?? $objectManager->get(State::class);
    }

    /**
     * Add generators to pool
     *
     * @param GeneratorInterface[] $generators
     *
     * @return void
     */
    protected function addGenerators(array $generators): void
    {
        foreach ($generators as $generator) {
            if (!$generator instanceof GeneratorInterface) {
                throw new \InvalidArgumentException(
                    \sprintf('Generator class must be an instance of %s', GeneratorInterface::class)
                );
            }
            $this->generators[$generator->getType()] = $generator;
        }
    }

    /**
     * Get generator
     *
     * @param string $type
     * @return GeneratorInterface
     * @throws \InvalidArgumentException
     */
    public function getGenerator(string $type): GeneratorInterface
    {
        if (!isset($this->generators[$type])) {
            throw new \InvalidArgumentException("Invalid generator type '{$type}'");
        }
        return $this->generators[$type];
    }

    /**
     * Traverse through all generators and generate all scheduled elements.
     *
     * @param Context $readerContext
     * @param GeneratorContext $generatorContext
     * @return $this
     */
    public function process(Context $readerContext, GeneratorContext $generatorContext): self
    {
        $this->buildStructure($readerContext->getScheduledStructure(), $generatorContext->getStructure());
        foreach ($this->generators as $generator) {
            $generator->process($readerContext, $generatorContext);
        }
        return $this;
    }

    /**
     * Build structure that is based on scheduled structure
     *
     * @param ScheduledStructure $scheduledStructure
     * @param LayoutDataStructure $structure
     * @return $this
     */
    protected function buildStructure(ScheduledStructure $scheduledStructure, LayoutDataStructure $structure): self
    {
        //Schedule all element into nested structure
        while (false === $scheduledStructure->isStructureEmpty()) {
            $this->helper->scheduleElement($scheduledStructure, $structure, \key($scheduledStructure->getStructure()));
        }
        $scheduledStructure->flushPaths();
        while (false === $scheduledStructure->isListToSortEmpty()) {
            $this->reorderElements($scheduledStructure, $structure, \key($scheduledStructure->getListToSort()));
        }
        foreach ($scheduledStructure->getListToMove() as $elementToMove) {
            $this->moveElementInStructure($scheduledStructure, $structure, $elementToMove);
        }
        foreach ($scheduledStructure->getListToRemove() as $elementToRemove) {
            $this->removeElement($scheduledStructure, $structure, $elementToRemove);
        }
        foreach ($scheduledStructure->getElements() as $name => $data) {
            [, $data] = $data;
            if ($this->visibilityConditionsExistsIn($data)) {
//                $condition = $this->conditionFactory->create($data['attributes']['visibilityConditions']);
//                if (!$condition->isVisible($data['attributes']['visibilityConditions'])) {
//                    $this->removeElement($scheduledStructure, $structure, $name);
//                }
            }
        }
        return $this;
    }

    /**
     * Reorder a child of a specified element.
     *
     * @param ScheduledStructure $scheduledStructure
     * @param LayoutDataStructure $structure
     * @param string $elementName
     * @return void
     */
    protected function reorderElements(
        ScheduledStructure  $scheduledStructure,
        LayoutDataStructure $structure,
                            $elementName
    ): void
    {
        $element = $scheduledStructure->getElementToSort($elementName);
        $scheduledStructure->unsetElementToSort($element[ScheduledStructure::ELEMENT_NAME]);

        if (isset($element[ScheduledStructure::ELEMENT_OFFSET_OR_SIBLING])) {
            $siblingElement = $scheduledStructure->getElementToSort(
                $element[ScheduledStructure::ELEMENT_OFFSET_OR_SIBLING]
            );

            if (isset($siblingElement[ScheduledStructure::ELEMENT_NAME])
                && $structure->hasElement($siblingElement[ScheduledStructure::ELEMENT_NAME])
            ) {
                $this->reorderElements(
                    $scheduledStructure,
                    $structure,
                    $siblingElement[ScheduledStructure::ELEMENT_NAME]
                );
            }
        }

//        $structure->reorderChildElement(
//            $element[ScheduledStructure::ELEMENT_PARENT_NAME],
//            $element[ScheduledStructure::ELEMENT_NAME],
//            $element[ScheduledStructure::ELEMENT_OFFSET_OR_SIBLING],
//            $element[ScheduledStructure::ELEMENT_IS_AFTER]
//        );
    }

    /**
     * Move element in scheduled structure
     *
     * @param ScheduledStructure $scheduledStructure
     * @param LayoutDataStructure $structure
     * @param string $element
     * @return $this
     */
    protected function moveElementInStructure(
        ScheduledStructure  $scheduledStructure,
        LayoutDataStructure $structure,
        string              $element
    )
    {
        [$destination, $siblingName, $isAfter, $alias] = $scheduledStructure->getElementToMove($element);
        $childAlias = $structure->getChildAlias($structure->getParentId($element), $element);
        if (!$alias && false === $structure->getChildId($destination, $childAlias)) {
            $alias = $childAlias;
        }
        $structure->unsetChild($element, $alias);
        try {
            $structure->setAsChild($element, $destination, $alias);
            $structure->reorderChildElement($destination, $element, $siblingName, $isAfter);
        } catch (\OutOfBoundsException $e) {
            if ($this->appState->getMode() === AppMode::Develop) {
                $this->logger->warning('Broken reference: ' . $e->getMessage());
            }
        }
        $scheduledStructure->unsetElementFromBrokenParentList($element);
        return $this;
    }

    /**
     * Remove scheduled element
     *
     * @param ScheduledStructure $scheduledStructure
     * @param LayoutDataStructure $structure
     * @param string $elementName
     * @param bool $isChild
     * @return $this
     */
    protected function removeElement(
        ScheduledStructure  $scheduledStructure,
        LayoutDataStructure $structure,
        string              $elementName,
        bool                $isChild = false
    ): self
    {
        $elementsToRemove = \array_keys($structure->getChildren($elementName));
        $scheduledStructure->unsetElement($elementName);
        foreach ($elementsToRemove as $element) {
            $this->removeElement($scheduledStructure, $structure, $element, true);
        }
        if (!$isChild) {
            $structure->unsetElement($elementName);
            $scheduledStructure->unsetElementFromListToRemove($elementName);
        }
        return $this;
    }

    /**
     * Check visibility conditions exists in data.
     *
     * @param array $data
     * @return bool
     */
    protected function visibilityConditionsExistsIn(array $data): bool
    {
        return isset($data['attributes']) &&
            \array_key_exists('visibilityConditions', $data['attributes']) &&
            !empty($data['attributes']['visibilityConditions']);
    }
}
