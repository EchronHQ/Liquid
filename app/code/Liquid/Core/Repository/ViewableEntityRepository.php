<?php
declare(strict_types=1);

namespace Liquid\Core\Repository;

use Liquid\Content\Model\Resource\AbstractViewableEntity;

interface ViewableEntityRepository
{
    /**
     * @return AbstractViewableEntity[]
     */
    public function getEntities(): array;
}
