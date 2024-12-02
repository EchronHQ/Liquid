<?php
declare(strict_types=1);

namespace Content\ViewModel;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Content\ViewModel\BaseViewModel;
use Liquid\Framework\View\Element\ArgumentInterface;

class PageViewModel extends BaseViewModel implements ArgumentInterface
{
    private AbstractViewableEntity|null $page;

    public function setViewableEntity(AbstractViewableEntity $entity): void
    {
        $this->page = $entity;
    }

    public function getViewableEntity(): AbstractViewableEntity
    {
        return $this->page;
    }
}
