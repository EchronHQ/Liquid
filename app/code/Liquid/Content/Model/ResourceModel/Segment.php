<?php
declare(strict_types=1);

namespace Liquid\Content\Model\ResourceModel;

class Segment
{
    public function readAllSegments(): array
    {
        return [
            [
                'id' => 'seg_0',
                'code' => 'default',
                'name' => 'Default Segment',
                'website_id' => 'web_0',
            ],
        ];
    }
}
