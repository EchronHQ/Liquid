<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Entity;

use Liquid\Content\Model\Resource\AbstractViewableEntity;

interface EntityResolverInterface
{
    /**
     * Get available entities
     *
     * @return AbstractViewableEntity[]
     */
    public function getEntities(): array;

    /**
     * Get entity by id
     *
     * @param string $entityId
     * @return AbstractViewableEntity|null
     */
    public function getEntity(string $entityId): AbstractViewableEntity|null;
}
