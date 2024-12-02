<?php
declare(strict_types=1);
return [
    'types' => [
        \Liquid\Framework\App\Area\AreaList::class => [
            'arguments' => [
                'areas' => [
                    'type' => 'array',
                    'value' => [
                        'adminhtml' => ['frontNameResolver' => '', 'router' => 'admin'],
                    ],
                ],
            ],
        ],
    ],
];
