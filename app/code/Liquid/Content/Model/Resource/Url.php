<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

class Url
{
    public string $request;
    public string $target;
    public string $entity_type;
    public int|string $entity_id;

    public function __construct(string $request, string $target, string $entity_type, int|string $entity_id)
    {
        $this->request = $request;
        $this->target = $target;
        $this->entity_type = $entity_type;
        $this->entity_id = $entity_id;
    }
}
