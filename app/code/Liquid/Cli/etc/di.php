<?php
declare(strict_types=1);

use Liquid\Framework\App\Area\AreaList;

return [
    'types' => [
        AreaList::class => [
            'arguments' => [
                'areas' => [
                    'type' => 'array',
                    'value' => [
                        'crontab' => null,
                    ],
                ],
            ],
        ],
    ],
];
