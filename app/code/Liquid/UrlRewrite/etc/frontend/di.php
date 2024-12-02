<?php
declare(strict_types=1);
return [
    'preferences' => [
        \Liquid\UrlRewrite\Model\UrlFinderInterface::class => \Liquid\UrlRewrite\Model\AggregateUrlFinder::class,
    ],
    'types' => [
        Liquid\Framework\App\Router\RouterList::class => [
            'arguments' => [
                'routerList' => [
                    'type' => 'array',
                    'value' => [
                        'urlrewrite' => [
                            'name' => 'urlrewrite',
                            'class' => \Liquid\UrlRewrite\Controller\Router::class,
                            'disabled' => false,
                            'sortOrder' => 20,
                        ],
                    ],
                ],
            ],
        ],

    ],
];
