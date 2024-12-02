<?php
declare(strict_types=1);

namespace Liquid\Content\ViewModel;

use Liquid\Content\Helper\LocaleHelper;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\Url;
use Liquid\Framework\View\Element\ArgumentInterface;

class Navigation implements ArgumentInterface
{
    public array $navigation;

    public function __construct(
        private readonly Resolver     $resolver,
        private readonly Url          $url,
        private readonly LocaleHelper $localeHelper
    )
    {


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
                                'icon' => 'asset/icons/menu/integrate.svg',
                                'link' => $this->url->getPageUrl('products/integrate'),
                            ],
                        ],
                    ],
                    [
                        'title' => 'Features',
                        'items' => [
                            [
                                'title' => 'Integrate',
                                'description' => 'Connect your platforms with ease and manage your data in one place so you can focus on growing your business.',
                                'icon' => 'asset/icons/menu/integrate.svg',
                                'link' => $this->url->getPageUrl('products/integrate'),
                            ],
                            [
                                'title' => 'Automate',
                                'description' => 'Automate your processes. Gain better insights and reduce mistakes.',
                                'icon' => 'asset/icons/menu/automate.svg',
                                'link' => $this->url->getPageUrl('products/automate'),
                            ],
                            [
                                'title' => $this->localeHelper->translate('Visualize'),
                                'description' => 'Monitor platforms, processes and data, identify issues and solve issues instantly.',
                                'icon' => 'asset/icons/menu/visualize.svg',
                                'link' => $this->url->getPageUrl('products/visualize'),
                            ],
                            [
                                'title' => 'Why Attlaz',
                                'description' => 'Giving your team superpowers',
                                'icon' => 'asset/icons/menu/why-attlaz.svg',
                                'link' => $this->url->getPageUrl('platform'),
                            ],
                            //                            [
                            //                                'title'       => 'How it works',
                            //                                'description' => 'Attlaz powers the pathways to accelerate your business.',
                            //                                'icon'        => $this->resolver->getFrontendFileUrl('asset/icons/menu/how-it-works.svg',
                            //                                'link'        => $this->resolver->getPageUrl('platform')
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
                        // TODO: make these items come from the use-case repository
                        'items' => [
                            [
                                'title' => 'Ecommerce',
                                'description' => 'Enhance your ecommerce',
                                'icon' => 'asset/icons/menu/ecommerce.svg',
                                'link' => $this->url->getPageUrl('use-cases/ecommerce'),
                            ],
                            [
                                'title' => 'Finance',
                                'description' => 'Streamline all your financial data',
                                'icon' => 'asset/icons/menu/ecommerce.svg',
                                'link' => $this->url->getPageUrl('use-cases/finance'),
                            ],
                            [
                                'title' => 'IT',
                                'description' => 'Centralize all your data and application integrations',
                                'icon' => 'asset/icons/menu/ecommerce.svg',
                                'link' => $this->url->getPageUrl('use-cases/it'),
                            ],
//
//                            [
//                                'title' => 'Data management',
//                                'description' => 'Manage your data',
//                                'icon' => $this->resolver->getFrontendFileImageUrl('asset/icons/menu/data-migration.svg',
//                                'link' => $this->url->getPageUrl('use-cases/data-management'),
//                            ],
                            [
                                'title' => 'Marketing',
                                'description' => 'Enable marketing automation',
                                'icon' => 'asset/icons/menu/marketing.svg',
                                'link' => $this->url->getPageUrl('use-cases/marketing'),
                            ],
                            [
                                'title' => 'Sales',
                                'description' => 'Connect all your sales processes',
                                'icon' => 'asset/icons/menu/marketing.svg',
                                'link' => $this->url->getPageUrl('use-cases/sales'),
                            ],
                            //                            ['title' => 'API reference', 'link' => $this->resolver->getPageUrl('api-reference')],
                            //                            ['title' => 'Platform status', 'link' => $this->getConfiguration()->getValue('status_url'), 'target' => '_blank', 'rel' => 'noopener'],
                        ],
                    ],
                    [
                        'title' => 'By application',
                        'items' => [
                            [
                                'title' => 'Adobe Commerce',
                                'description' => '',
                                'icon' => 'asset/icons/menu/ecommerce.svg',
                                'link' => $this->url->getPageUrl('use-cases/ecommerce'),
                            ],

                            [
                                'title' => 'Shopify',
                                'description' => '',
                                'icon' => 'asset/icons/menu/data-migration.svg',
                                'link' => $this->url->getPageUrl('use-cases/data-management'),
                            ],
//                            [
//                                'title' => 'Marketing',
//                                'description' => 'Make faster decisions',
//                                'icon' => $this->resolver->getFrontendFileImageUrl('asset/icons/menu/marketing.svg',
//                                'link' => $this->resolver->getPageUrl('use-cases/marketing'),
//                            ],
                            //                            ['title' => 'API reference', 'link' => $this->resolver->getPageUrl('api-reference')],
                            //                            ['title' => 'Platform status', 'link' => $this->getConfiguration()->getValue('status_url'), 'target' => '_blank', 'rel' => 'noopener'],
                        ],
                    ],
                ],
//                'footer' => [
//                    'title' => 'Customers',
//                    'description' => 'See how ' . $this->translate('organizations') . ' are using the Attlaz platform to achieve their goals.',
//                    'icon' => $this->resolver->getFrontendFileImageUrl('asset/icons/menu/customers.svg',
//                    'link' => $this->resolver->getPageUrl('case-studies'),
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
                                'icon' => 'asset/icons/menu/about.svg',
                                'link' => $this->url->getPageUrl('about'),
                            ],
                            [
                                'title' => 'Careers',
                                'description' => 'Work with purpose. Leave your mark.',
                                'icon' => 'asset/icons/menu/careers.svg',
                                'link' => $this->url->getPageUrl('careers'),
                            ],
                            [
                                'title' => 'Partners',
                                'description' => 'Learn how Attlaz partners are accelerating businesses',
                                'icon' => 'asset/icons/menu/partners.svg',
                                'link' => $this->url->getPageUrl('partners'),
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
            //                            ['title' => 'Documentation', 'link' => $this->resolver->getPageUrl('documentation')],
            //                            ['title' => 'API reference', 'link' => $this->resolver->getPageUrl('api-reference')],
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
//                                'icon' => $this->resolver->getFrontendFileImageUrl('asset/icons/menu/blog.svg',
//                                'link' => $this->resolver->getPageUrl('blog'),
//                            ],
//                            [
//                                'title' => 'Documentation',
//                                'description' => 'Access Attlaz documentation',
//                                'icon' => $this->resolver->getFrontendFileImageUrl('asset/icons/menu/documentation.svg',
//                                'link' => $this->resolver->getPageUrl('docs'),
//                                'target' => '_blank',
//                                'rel' => 'noopener',
//                            ],
//                            [
//                                'title' => 'Help center',
//                                'description' => 'We are here to help you',
//                                'icon' => $this->resolver->getFrontendFileImageUrl('asset/icons/menu/support.svg',
//                                'link' => $this->resolver->getPageUrl('support'),
//                            ],
//                        ],
//                    ],
                    [
                        'title' => 'Resources',
                        'items' => [
                            [
                                'title' => 'Customer stories',
                                'description' => 'See how ' . $this->localeHelper->translate('organizations') . ' are using the Attlaz platform to achieve their goals.',
                                'icon' => 'asset/icons/menu/customers.svg',
                                'link' => $this->url->getPageUrl('case-studies'),
                            ],
                            [
                                'title' => 'Blog',
                                'description' => 'Read up on all things integrating, automating, and much more',
                                'icon' => 'asset/icons/menu/blog.svg',
                                'link' => $this->url->getPageUrl('blog'),
                            ],
                            [
                                'title' => 'Documentation',
                                'description' => 'Access Attlaz documentation',
                                'icon' => 'asset/icons/menu/documentation.svg',
                                'link' => $this->url->getPageUrl('docs'),
                                'target' => '_blank',
                                'rel' => 'noopener',
                            ],
                            [
                                'title' => 'Help center',
                                'description' => 'We are here to help you',
                                'icon' => 'asset/icons/menu/support.svg',
                                'link' => $this->url->getPageUrl('support'),
                            ],
                        ],
                    ],
                ],
            ],
            //            [
            //                'title' => 'Plans',
            //                'type'  => 'link',
            //                'link'  => $this->resolver->getPageUrl('plans')
            //            ]
        ];
    }

}
