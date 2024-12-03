<?php
declare(strict_types=1);

return [
    'types' => [
        \Liquid\Framework\App\Router\RouterList::class => [
            'arguments' => [
                'routerList' => [
                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_ARRAY,
                    'value' => [
                        'admin' => [
                            'name' => 'admin',
                            'class' => \Liquid\Admin\App\Router::class,
                            'sortOrder' => 10,
                        ],
                        'default' => [
                            'name' => 'fallback',
                            'class' => \Liquid\Framework\App\Router\FallbackRouter::class,
                            'sortOrder' => 100,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
