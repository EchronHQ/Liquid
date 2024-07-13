<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

class PageDefinition extends AbstractViewableEntity
{
    public function __construct(int|string $id)
    {
        parent::__construct($id);
        $this->urlRewrites = ['/content/page/view/page-id/' . $this->id];
    }
}
