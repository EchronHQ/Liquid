<?php
declare(strict_types=1);

use Liquid\Blog\Model\Storage\EntityResolver;
use Liquid\Blog\Model\Storage\UrlRewrite;
use Liquid\Framework\App\Entity\AggregateEntityResolver;
use Liquid\UrlRewrite\Model\AggregateUrlFinder;

return [
    'types' => [
        AggregateEntityResolver::class => [
            'arguments' => [
                'children' => [
                    'type' => 'array',
                    'value' => [
                        'blog' => [
                            'class' => EntityResolver::class,
                        ],
                    ],
                ],
            ],
        ],
        AggregateUrlFinder::class => [
            'arguments' => [
                'children' => [
                    'type' => 'array',
                    'value' => [
                        'blog' => [
                            'class' => UrlRewrite::class,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
