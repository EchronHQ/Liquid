<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

class PageDefinition extends AbstractViewableEntity
{
    protected string $controllerEndpoint = 'content/page/view/page-id/';

    public function __construct(
        int|string $id
    )
    {
        parent::__construct($id);
    }
}
