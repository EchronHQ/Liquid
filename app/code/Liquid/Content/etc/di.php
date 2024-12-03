<?php
declare(strict_types=1);

use Liquid\Framework\App\Entity\AggregateEntityResolver;

return [
    'types' => [
        \Liquid\Framework\App\Area\AreaList::class => [
            'arguments' => [
                'areas' => [
                    'type' => 'array',
                    'value' => [
                        'frontend' => [
                            'code' => \Liquid\Framework\App\Area\AreaCode::Frontend,
                            'frontName' => null,
                            'router' => 'standard',
                        ],
                    ],
                ],
                'defaultAreaCode' => [
                    'type' => 'const',
                    'value' => \Liquid\Framework\App\Area\AreaCode::Frontend,
                ],
            ],
        ],
        \Liquid\Framework\App\Router\RouterList::class => [
            'arguments' => [
                'routerList' => [
                    'type' => 'array',
                    'value' => [
                        'default' => [
                            'name' => 'default',
                            'class' => \Liquid\Framework\App\Router\BaseRouter::class,
                            'sortOrder' => 30,
                        ],
                        'fallback' => [
                            'name' => 'fallback',
                            'class' => \Liquid\Framework\App\Router\FallbackRouter::class,
                            'sortOrder' => 100,
                        ],
                    ],
                ],
            ],
        ],
        \Liquid\Framework\View\Layout\ReaderPool::class => [
            'arguments' => [
                'readers' => [
                    'type' => 'array',
                    'value' => [
//                        'html' => \Liquid\Framework\View\Page\Config\Generator\Html::class,
//                        'head' => \Liquid\Framework\View\Page\Config\Generator\Head::class,
//                        'body' => \Liquid\Framework\View\Page\Config\Generator\Body::class,
                    ],
                ],
//                'container' => [
//                    'type' => 'sting',
//                    'value' => 'Magento_Theme::root.phtml',
//                ],
            ],
        ],
        \Liquid\UrlRewrite\Model\AggregateUrlFinder::class => [
            'arguments' => [
                'children' => [
                    'type' => 'array',
                    'value' => [
                        'contentitems' => [
                            'class' => \Liquid\Content\Model\Storage\UrlRewrite::class,
                        ],
                    ],
                ],
            ],
        ],
        AggregateEntityResolver::class => [
            'arguments' => [
                'children' => [
                    'type' => 'array',
                    'value' => [
                        'pages' => [
                            'class' => \Liquid\Content\Model\Storage\EntityResolver::class,
                        ],
                    ],
                ],
            ],
        ],
        \Liquid\Framework\App\Router\NoRouteHandlerList::class => [
            'arguments' => [
                'handlerClassesList' => [
                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_ARRAY,
                    'value' => [
                        'default' => [
                            'class' => \Liquid\Framework\App\Router\NoRouteHandler::class,
                            'sortOrder' => 100,
                        ],
                    ],
                ],
            ],
        ],
//        \Liquid\Framework\View\Result\Page::class => [
//            'arguments' => [
//                'layoutReaderPool' => [
//                    'type' => 'object',
//                    'value' => 'pageConfigRenderPool',
//                ],
//                'generatorPool' => [
//                    'type' => 'object',
//                    'value' => 'pageLayoutGeneratorPool',
//                ],
//                'template' => [
//                    'type' => 'object',
//                    'value' => 'Magento_Theme::root.phtml',
//                ],
//            ],
//        ],
//        'x' => [
//            'type' => ModuleFileCollector::class,
//            'arguments' => [
//                'subDir' => [
//                    'type' => 'string',
//                    'value' => 'layout',
//                ],
//            ],
//        ],
//        \Liquid\Framework\View\Layout\File\Collector\Aggregated::class => [
//            'arguments' => [
//                'moduleFileCollector' => [
//                    'type' => 'object',
//                    'value' => 'x',
//                ],
//            ],
//
//        ],
    ],
];
