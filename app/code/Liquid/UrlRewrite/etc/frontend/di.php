<?php
declare(strict_types=1);

use Liquid\Framework\App\Router\RouterList;
use Liquid\UrlRewrite\Controller\Router;
use Liquid\UrlRewrite\Model\AggregateUrlFinder;
use Liquid\UrlRewrite\Model\UrlFinderInterface;

return [
    'preferences' => [
        UrlFinderInterface::class => AggregateUrlFinder::class,
    ],
    'types' => [
        RouterList::class => [
            'arguments' => [
                'routerList' => [
                    'type' => 'array',
                    'value' => [
                        'urlrewrite' => [
                            'name' => 'urlrewrite',
                            'class' => Router::class,
                            'disabled' => false,
                            'sortOrder' => 20,
                        ],
                    ],
                ],
            ],
        ],

    ],
];
