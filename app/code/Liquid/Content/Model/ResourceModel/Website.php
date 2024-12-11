<?php
declare(strict_types=1);

namespace Liquid\Content\Model\ResourceModel;

class Website
{
    public function readAllWebsites(): array
    {
        return [
            [
                'id' => 'web_0',
                'code' => 'default',
                'name' => 'Default Website',
            ],
        ];
    }
}
