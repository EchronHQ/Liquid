<?php
declare(strict_types=1);

namespace Liquid\Framework\Config;

use PHPUnit\Framework\TestCase;

class ConfigMergerTest extends TestCase
{
    public function testBasicCollections()
    {
        $configA = ['a' => '1'];
        $configB = ['b' => '2'];
        $expected = ['a' => '1', 'b' => '2'];

        $merger = new ConfigMerger($configA);
        $merger->merge($configB);

        $this->assertSame($expected, $merger->getData());
    }

    public function testComplexCollections()
    {
        $configA = [
            'types' => [
                'Type A' => [
                    'arguments' => [
                        'argumentA' => [
                            'type' => 'string',
                            'value' => 'argumentAvalue',
                        ],
                    ],
                ],
            ],
        ];
        $configB = [
            'types' => [
                'Type A' => [
                    'arguments' => [
                        'argumentB' => [
                            'type' => 'string',
                            'value' => 'argumentBvalue',
                        ],
                    ],
                ],
            ],
        ];
        $expected = [
            'types' => [
                'Type A' => [
                    'arguments' => [
                        'argumentA' => [
                            'type' => 'string',
                            'value' => 'argumentAvalue',
                        ],
                        'argumentB' => [
                            'type' => 'string',
                            'value' => 'argumentBvalue',
                        ],
                    ],
                ],
            ],
        ];

        $merger = new ConfigMerger($configA);
        $merger->merge($configB);

        $this->assertSame($expected, $merger->getData());
    }

    public function testComplexCollections2()
    {
        $configA = [
            'types' => [
                'Type A' => [
                    'arguments' => [
                        [
                            'type' => 'string',
                            'value' => 'argumentAvalue',
                        ],

                    ],
                ],
            ],
        ];
        $configB = [
            'types' => [
                'Type A' => [
                    'arguments' => [
                        [
                            [
                                'type' => 'string',
                                'value' => 'argumentBvalue',
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $expected = [
            'types' => [
                'Type A' => [
                    'arguments' => [
                        [
                            'type' => 'string',
                            'value' => 'argumentAvalue',
                        ],
                        [
                            'type' => 'string',
                            'value' => 'argumentBvalue',
                        ],
                    ],
                ],
            ],
        ];

        $merger = new ConfigMerger($configA);
        $merger->merge($configB);

        $this->assertSame($expected, $merger->getData());
    }
}
