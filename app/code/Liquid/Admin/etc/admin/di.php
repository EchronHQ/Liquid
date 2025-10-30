<?php
declare(strict_types=1);

use Liquid\Admin\App\Router;
use Liquid\Framework\App\Router\FallbackRouter;
use Liquid\Framework\App\Router\RouterList;
use Liquid\Framework\ObjectManager\Config;

return [
    'types' => [
        RouterList::class => [
            'arguments' => [
                'routerList' => [
                    'type' => Config::$TYPE_ARRAY,
                    'value' => [
                        'admin' => [
                            'name' => 'admin',
                            'class' => Router::class,
                            'sortOrder' => 10,
                        ],
                        'default' => [
                            'name' => 'fallback',
                            'class' => FallbackRouter::class,
                            'sortOrder' => 100,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
