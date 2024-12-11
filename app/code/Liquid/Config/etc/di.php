<?php
declare(strict_types=1);

use Liquid\Framework\App\Config\ConfigSourceInterface;

return [
    'types' => [
        \Liquid\Framework\App\Config\ScopeConfig::class => [
            'arguments' => [
                'types' => [
                    'type' => 'array',
                    'value' => [
                        'system' => [
                            'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
                            'value' => \Liquid\Config\App\Config\Type\System::class,
                        ],
                    ],
                ],
            ],
        ],
        \Liquid\Config\App\Config\Type\System::class => [
            'arguments' => [
                'cache' => [
                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
                    'value' => Liquid\Framework\App\Cache\Type\Config::class,
                ],
                'reader' => [
                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
                    'value' => \Liquid\Config\App\Config\Type\SystemConfigReader::class,
                ],
            ],
        ],
        \Liquid\Config\App\Config\Source\ConfigSourceAggregated::class => [
            'arguments' => [
                'sources' => [
                    'type' => 'array',
                    'value' => [
                        'modular' => [
                            'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_ARRAY,
                            'value' => [
                                'sortOrder' => 0,
                                'source' => [
                                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
                                    'value' => \Liquid\Config\App\Config\Source\ModularConfigSource::class,
                                ],
                            ],
                        ],
                        'dynamic' => [
                            'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_ARRAY,
                            'value' => [
                                'sortOrder' => 50,
                                'source' => [
                                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
                                    'value' => \Liquid\Config\App\Config\Source\RuntimeConfigSource::class,
                                ],
                            ],
                        ],
                        'initial' => [
                            'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_ARRAY,
                            'value' => [
                                'sortOrder' => 100,
                                'source' => [
                                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
                                    'value' => \Liquid\Framework\App\Config\InitialConfigSource::class,
                                ],
                            ],
                        ],
//                        'dynamic' => [
//                            'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
//                            'value' => \Liquid\Config\App\Config\Type\System::class,
//                        ],
//                        'initial' => [
//                            'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
//                            'value' => \Liquid\Config\App\Config\Type\System::class,
//                        ],
                    ],
                ],
            ],
        ],
        \Liquid\Framework\App\Config\InitialConfigSource::class => [
            'arguments' => [
//                'cache' => [
//                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_OBJECT,
//                    'value' => Liquid\Framework\App\Cache\Type\Config::class,
//                ],
                'configType' => [
                    'type' => \Liquid\Framework\ObjectManager\Config::$TYPE_CONST,
                    'value' => \Liquid\Config\App\Config\Type\System::CONFIG_TYPE,
                ],
            ],
        ],
    ],
    'preferences' => [
        ConfigSourceInterface::class => \Liquid\Config\App\Config\Source\ConfigSourceAggregated::class,
    ],
];
