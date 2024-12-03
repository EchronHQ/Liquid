<?php
declare(strict_types=1);
return [
    'types' => [
        \Liquid\Framework\App\Area\AreaList::class => [
            'arguments' => [
                'areas' => [
                    'type' => 'array',
                    'value' => [
                        'admin' => [
                            'code' => \Liquid\Framework\App\Area\AreaCode::Admin,
                            'frontNameResolver' => \Liquid\Admin\App\Area\FrontNameResolver::class,
                            'router' => 'admin',
                        ],
                    ],
                ],
            ],
        ],
        \Liquid\Framework\App\Router\NoRouteHandlerList::class => [
            'arguments' => [
                'handlerClassesList' => [
                    'type' => 'array',
                    'value' => [
                        'admin' => [
                            'class' => \Liquid\Admin\App\Router\NoRouteHandler::class,
                            'sortOrder' => 10,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
