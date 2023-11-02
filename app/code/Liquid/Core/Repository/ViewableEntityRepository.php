<?php
declare(strict_types=1);

namespace Liquid\Core\Repository;

use Liquid\Content\Model\Resource\PageDefinition;

interface ViewableEntityRepository
{
    /**
     * @return PageDefinition[]
     */
    public function getEntities(): array;
}
