<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Content\Block\TemplateBlock;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Core\Model\BlockContext;

class Navigation extends TemplateBlock
{
    public array $navigation;

    public function __construct(BlockContext $context, TemplateHelper $templateHelper)
    {
        parent::__construct($context, $templateHelper);

        $this->navigation = [
            [
                'title' => 'Platform',
                'type' => 'dropdown',
                'subsections' => [
                    [
                        'title' => 'Products',
                        'items' => [
                            [
                                'title' => 'Integrate',
                                'description' => 'Connect your platforms with ease and manage your data in one place so you can focus on growing your business.',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/integrate.svg'),
                                'link' => $this->getResolver()->getPageUrl('products/integrate'),
                            ],
                            [
                                'title' => 'Automate',
                                'description' => 'Automate your processes. Gain better insights and reduce mistakes.',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/automate.svg'),
                                'link' => $this->getResolver()->getPageUrl('products/automate'),
                            ],
                            [
                                'title' => $this->translate('Visualize'),
                                'description' => 'Monitor platforms, processes and data, identify issues and solve issues instantly.',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/visualize.svg'),
                                'link' => $this->getResolver()->getPageUrl('products/visualize'),
                            ],
                        ],
                    ],
                    [
//                        'title' => 'Overview',
                        'items' => [
                            [
                                'title' => 'Why Attlaz',
                                'description' => 'Giving your team superpowers',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/why-attlaz.svg'),
                                'link' => $this->getResolver()->getPageUrl('platform'),
                            ],
                            //                            [
                            //                                'title'       => 'How it works',
                            //                                'description' => 'Attlaz powers the pathways to accelerate your business.',
                            //                                'icon'        => $this->getResolver()->getFrontendFileUrl('asset/icons/menu/how-it-works.svg'),
                            //                                'link'        => $this->getResolver()->getPageUrl('platform')
                            //                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Solutions',
                'type' => 'dropdown',
                'subsections' => [
                    [
                        'title' => 'By use case',
                        'items' => [
                            [
                                'title' => 'Ecommerce',
                                'description' => 'Enhance your ecommerce',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/ecommerce.svg'),
                                'link' => $this->getResolver()->getPageUrl('use-cases/ecommerce'),
                            ],

                            [
                                'title' => 'Data management',
                                'description' => 'Manage your data',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/data-migration.svg'),
                                'link' => $this->getResolver()->getPageUrl('use-cases/data-management'),
                            ],
                            [
                                'title' => 'Marketing',
                                'description' => 'Make faster decisions',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/marketing.svg'),
                                'link' => $this->getResolver()->getPageUrl('use-cases/marketing'),
                            ],
                            //                            ['title' => 'API reference', 'link' => $this->getResolver()->getPageUrl('api-reference')],
                            //                            ['title' => 'Platform status', 'link' => $this->getConfiguration()->getValue('status_url'), 'target' => '_blank', 'rel' => 'noopener'],
                        ],
                    ],
                    [
                        'title' => 'By application',
                        'items' => [
                            [
                                'title' => 'Adobe Commerce',
                                'description' => '',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/ecommerce.svg'),
                                'link' => $this->getResolver()->getPageUrl('use-cases/ecommerce'),
                            ],

                            [
                                'title' => 'Shopify',
                                'description' => '',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/data-migration.svg'),
                                'link' => $this->getResolver()->getPageUrl('use-cases/data-management'),
                            ],
//                            [
//                                'title' => 'Marketing',
//                                'description' => 'Make faster decisions',
//                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/marketing.svg'),
//                                'link' => $this->getResolver()->getPageUrl('use-cases/marketing'),
//                            ],
                            //                            ['title' => 'API reference', 'link' => $this->getResolver()->getPageUrl('api-reference')],
                            //                            ['title' => 'Platform status', 'link' => $this->getConfiguration()->getValue('status_url'), 'target' => '_blank', 'rel' => 'noopener'],
                        ],
                    ],
                ],
//                'footer' => [
//                    'title' => 'Customers',
//                    'description' => 'See how ' . $this->translate('organizations') . ' are using the Attlaz platform to achieve their goals.',
//                    'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/customers.svg'),
//                    'link' => $this->getResolver()->getPageUrl('case-studies'),
//                ],
            ],
            [
                'title' => 'About',
                'type' => 'dropdown',
                'subsections' => [
                    [
                        'title' => 'About Attlaz',
                        'items' => [
                            [
                                'title' => 'About',
                                'description' => 'Discover what Attlaz is all about',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/about.svg'),
                                'link' => $this->getResolver()->getPageUrl('about'),
                            ],
                            [
                                'title' => 'Careers',
                                'description' => 'Work with purpose. Leave your mark.',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/careers.svg'),
                                'link' => $this->getResolver()->getPageUrl('careers'),
                            ],
                            [
                                'title' => 'Partners',
                                'description' => 'Learn how Attlaz partners are accelerating businesses',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/partners.svg'),
                                'link' => $this->getResolver()->getPageUrl('partners'),
                            ],
                        ],
                    ],
                ],
            ],
            //            [
            //                'title'       => 'Developers',
            //                'type'        => 'dropdown',
            //                'subsections' => [
            //                    [
            //                        'title' => 'Developer platform',
            //                        'items' => [
            //                            ['title' => 'Documentation', 'link' => $this->getResolver()->getPageUrl('documentation')],
            //                            ['title' => 'API reference', 'link' => $this->getResolver()->getPageUrl('api-reference')],
            //                            ['title' => 'Platform status', 'link' => $this->getConfiguration()->getValue('status_url'), 'target' => '_blank', 'rel' => 'noopener'],
            //                        ]
            //                    ]
            //                ]
            //            ],
            [
                'title' => 'Resources',
                'type' => 'dropdown',
                'subsections' => [
//                    [
//                        'title' => 'Resources',
//                        'items' => [
//                            [
//                                'title' => 'Blog',
//                                'description' => 'Read up on all things integrating, automating, and much more',
//                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/blog.svg'),
//                                'link' => $this->getResolver()->getPageUrl('blog'),
//                            ],
//                            [
//                                'title' => 'Documentation',
//                                'description' => 'Access Attlaz documentation',
//                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/documentation.svg'),
//                                'link' => $this->getResolver()->getPageUrl('docs'),
//                                'target' => '_blank',
//                                'rel' => 'noopener',
//                            ],
//                            [
//                                'title' => 'Help center',
//                                'description' => 'We are here to help you',
//                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/support.svg'),
//                                'link' => $this->getResolver()->getPageUrl('support'),
//                            ],
//                        ],
//                    ],
                    [
                        'title' => 'Resources',
                        'items' => [
                            [
                                'title' => 'Customer stories',
                                'description' => 'See how ' . $this->translate('organizations') . ' are using the Attlaz platform to achieve their goals.',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/customers.svg'),
                                'link' => $this->getResolver()->getPageUrl('case-studies'),
                            ],
                            [
                                'title' => 'Blog',
                                'description' => 'Read up on all things integrating, automating, and much more',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/blog.svg'),
                                'link' => $this->getResolver()->getPageUrl('blog'),
                            ],
                            [
                                'title' => 'Documentation',
                                'description' => 'Access Attlaz documentation',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/documentation.svg'),
                                'link' => $this->getResolver()->getPageUrl('docs'),
                                'target' => '_blank',
                                'rel' => 'noopener',
                            ],
                            [
                                'title' => 'Help center',
                                'description' => 'We are here to help you',
                                'icon' => $this->getResolver()->getFrontendFileImageUrl('asset/icons/menu/support.svg'),
                                'link' => $this->getResolver()->getPageUrl('support'),
                            ],
                        ],
                    ],
                ],
            ],
            //            [
            //                'title' => 'Plans',
            //                'type'  => 'link',
            //                'link'  => $this->getResolver()->getPageUrl('plans')
            //            ]
        ];
    }
}
