<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Storage;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Content\Repository\PageRepository;
use Liquid\Framework\App\Entity\EntityResolverInterface;

class EntityResolver implements EntityResolverInterface
{
    public function __construct(
        private readonly PageRepository $pageRepository
    )
    {

    }

    public function getEntity(string $entityId): AbstractViewableEntity|null
    {
        // TODO: implement this better (with caching, etc)
        $entities = $this->getEntities();
        foreach ($entities as $entity) {
            if ($entity->id === $entityId) {
                return $entity;
            }
        }
        return null;
    }

    public function getEntities(): array
    {
        return $this->pageRepository->getEntities();
    }
}
