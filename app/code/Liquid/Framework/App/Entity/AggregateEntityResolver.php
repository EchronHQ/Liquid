<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Entity;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class AggregateEntityResolver implements EntityResolverInterface
{
    // TODO: move caching mechanism to separate class so it can wrap around AggregateEntityResolver


    /**
     * @param array{class:string} $children
     */
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly array                  $children = []
    )
    {
    }

    public function getEntity(string $entityId): AbstractViewableEntity|null
    {

        foreach ($this->children as $child) {
            /** @var EntityResolverInterface $entityResolver */
            $entityResolver = $this->objectManager->get($child['class']);
            if (!$entityResolver instanceof EntityResolverInterface) {
                throw new \Exception('Entity resolver must implement `' . EntityResolverInterface::class . '` interface');
            }
            $entity = $entityResolver->getEntity($entityId);
            if ($entity !== null) {
                return $entity;
            }
        }
        return null;
    }

    public function getEntities(): array
    {

        $result = [];
        foreach ($this->children as $child) {
            /** @var EntityResolverInterface $entityResolver */
            $entityResolver = $this->objectManager->get($child['class']);
            if (!$entityResolver instanceof EntityResolverInterface) {
                throw new \Exception('Entity resolver must implement `' . EntityResolverInterface::class . '` interface');
            }
            $entities = $entityResolver->getEntities();
            // TODO: validate that we don't have duplicate entity ids
            foreach ($entities as $entity) {
                $result[] = $entity;
            }
        }
        return $result;
    }
}
