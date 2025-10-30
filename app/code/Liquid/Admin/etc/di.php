<?php
declare(strict_types=1);

use Liquid\Admin\App\Area\FrontNameResolver;
use Liquid\Admin\App\Router\NoRouteHandler;
use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\Area\AreaList;
use Liquid\Framework\App\Router\NoRouteHandlerList;

return [
    'types' => [
        AreaList::class => [
            'arguments' => [
                'areas' => [
                    'type' => 'array',
                    'value' => [
                        'admin' => [
                            'code' => AreaCode::Admin,
                            'frontNameResolver' => FrontNameResolver::class,
                            'router' => 'admin',
                        ],
                    ],
                ],
            ],
        ],
        NoRouteHandlerList::class => [
            'arguments' => [
                'handlerClassesList' => [
                    'type' => 'array',
                    'value' => [
                        'admin' => [
                            'class' => NoRouteHandler::class,
                            'sortOrder' => 10,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
