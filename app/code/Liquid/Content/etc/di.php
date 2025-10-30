<?php
declare(strict_types=1);

use Liquid\Content\App\Config\Type\Scopes;
use Liquid\Content\Model\Resolver\Segment;
use Liquid\Content\Model\Storage\EntityResolver;
use Liquid\Content\Model\Storage\UrlRewrite;
use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\Area\AreaList;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Entity\AggregateEntityResolver;
use Liquid\Framework\App\Router\BaseRouter;
use Liquid\Framework\App\Router\FallbackRouter;
use Liquid\Framework\App\Router\NoRouteHandler;
use Liquid\Framework\App\Router\NoRouteHandlerList;
use Liquid\Framework\App\Router\RouterList;
use Liquid\Framework\App\Scope\ScopeResolverInterface;
use Liquid\Framework\ObjectManager\Config;
use Liquid\Framework\View\Layout\ReaderPool;
use Liquid\UrlRewrite\Model\AggregateUrlFinder;

return [
    'preferences' => [
        ScopeResolverInterface::class => Segment::class,
    ],
    'types' => [
        AreaList::class => [
            'arguments' => [
                'areas' => [
                    'type' => 'array',
                    'value' => [
                        'frontend' => [
                            'code' => AreaCode::Frontend,
                            'frontName' => null,
                            'router' => 'standard',
                        ],
                    ],
                ],
                'defaultAreaCode' => [
                    'type' => 'const',
                    'value' => AreaCode::Frontend,
                ],
            ],
        ],
        RouterList::class => [
            'arguments' => [
                'routerList' => [
                    'type' => 'array',
                    'value' => [
                        'default' => [
                            'name' => 'default',
                            'class' => BaseRouter::class,
                            'sortOrder' => 30,
                        ],
                        'fallback' => [
                            'name' => 'fallback',
                            'class' => FallbackRouter::class,
                            'sortOrder' => 100,
                        ],
                    ],
                ],
            ],
        ],
        ReaderPool::class => [
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
        AggregateUrlFinder::class => [
            'arguments' => [
                'children' => [
                    'type' => 'array',
                    'value' => [
                        'contentitems' => [
                            'class' => UrlRewrite::class,
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
                            'class' => EntityResolver::class,
                        ],
                    ],
                ],
            ],
        ],
        NoRouteHandlerList::class => [
            'arguments' => [
                'handlerClassesList' => [
                    'type' => Config::$TYPE_ARRAY,
                    'value' => [
                        'default' => [
                            'class' => NoRouteHandler::class,
                            'sortOrder' => 100,
                        ],
                    ],
                ],
            ],
        ],
        ScopeConfig::class => [
            'arguments' => [
                'types' => [
                    'type' => 'array',
                    'value' => [
                        'scopes' => [
                            'type' => Config::$TYPE_OBJECT,
                            'value' => Scopes::class,
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
