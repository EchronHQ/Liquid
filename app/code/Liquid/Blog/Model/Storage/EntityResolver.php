<?php
declare(strict_types=1);

namespace Liquid\Blog\Model\Storage;

use Liquid\Blog\Repository\BlogRepository;
use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Framework\App\Entity\EntityResolverInterface;

class EntityResolver implements EntityResolverInterface
{
    public function __construct(
        private readonly BlogRepository        $blogRepository,
        private readonly TerminologyRepository $terminologyRepository,
    )
    {

    }

    public function getEntity(string $entityId): AbstractViewableEntity|null
    {
        // TODO: implement this better (with caching, etc)
        $entities = $this->getEntities();
        return array_find($entities, fn(AbstractViewableEntity $entity) => $entity->id === $entityId);
    }

    public function getEntities(): array
    {
        return $this->blogRepository->getEntities();
    }
}
