<?php
declare(strict_types=1);
return [
    'types' => [
        \Liquid\Framework\App\Entity\AggregateEntityResolver::class => [
            'arguments' => [
                'children' => [
                    'type' => 'array',
                    'value' => [
                        'blog' => [
                            'class' => \Liquid\Blog\Model\Storage\EntityResolver::class,
                        ],
                    ],
                ],
            ],
        ],
        \Liquid\UrlRewrite\Model\AggregateUrlFinder::class => [
            'arguments' => [
                'children' => [
                    'type' => 'array',
                    'value' => [
                        'blog' => [
                            'class' => \Liquid\Blog\Model\Storage\UrlRewrite::class,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
