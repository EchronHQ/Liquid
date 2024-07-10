<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

class PageDefinition extends AbstractViewableEntity
{
    public function getUrlRewrites(): array
    {
        return ['/content/page/view/page-id/' . $this->id];
    }
}
