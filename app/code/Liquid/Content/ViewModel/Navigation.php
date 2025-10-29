<?php
declare(strict_types=1);

namespace Liquid\Content\ViewModel;

use Attlaz\Connector\Model\PlatformDefinition;
use Liquid\Content\Helper\LocaleHelper;
use Liquid\Content\Helper\ViewableEntity;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\Url;
use Liquid\Framework\View\Element\ArgumentInterface;

class Navigation implements ArgumentInterface
{
    public array $navigation;

    public function __construct(
        private readonly Resolver       $resolver,
        private readonly Url            $url,
        private readonly ViewableEntity $viewableEntityHelper,
        private readonly LocaleHelper   $localeHelper
    )
    {


        $this->navigation = [
            [
                'title' => 'Platform',
                'type' => 'dropdown',
                // 'template' => 'Liquid_Content::navigation/platform.phtml',
                'subsections' => [
                    [
                        'title' => 'Platform Overview',
                        'items' => [
                            [
                                'title' => 'Bespoke Platform',
                                'description' => 'Understand our unique platform’s focus on data integration, automation and visualisation.',
                                // 'icon' => 'asset/icons/menu/feed-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('platform'),
                            ],
                            [
                                'title' => 'Why Attlaz?',
                                'description' => 'See what makes Attlaz stand out.',
                                // 'icon' => 'asset/icons/menu/market-sentry.svg',
                                'link' => $this->viewableEntityHelper->getUrl('why-attlaz'),
                            ],
                            [
                                'title' => 'Pricing',
                                'description' => 'Find the best package for you.',
                                // 'icon' => 'asset/icons/menu/inventory-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('plans'),
                            ],
                        ],
                    ],
                    [
                        'title' => 'Key Capabilities',
                        'items' => [
                            [
                                'title' => 'Unified Data Integration Hub',
//                                'description' => 'Streamline product distribution',
                                //  'icon' => 'asset/icons/menu/feed-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('integrations'),
                            ],
                            [
                                'title' => 'Connector Marketplace',
//                                'description' => 'Track market dynamics',
                                //   'icon' => 'asset/icons/menu/market-sentry.svg',
                                'link' => $this->viewableEntityHelper->getUrl('platform/connector-marketplace'),
                            ],
                            [
                                'title' => 'Real-Time Monitoring',
//                                'description' => 'Optimize inventory decisions',
                                // 'icon' => 'asset/icons/menu/inventory-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('platform/real-time-monitoring'),
                            ],
                            [
                                'title' => 'Error Management',
//                                'description' => 'Optimize inventory decisions',
                                //   'icon' => 'asset/icons/menu/inventory-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('platform/error-management'),
                            ],
                            [
                                'title' => 'Advanced Workflow Automation',
//                                'description' => 'Optimize inventory decisions',
                                //  'icon' => 'asset/icons/menu/inventory-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('platform/workflow-automation'),
                            ],
                            [
                                'title' => 'Cross-Application Connectivity',
//                                'description' => 'Optimize inventory decisions',
                                //'icon' => 'asset/icons/menu/inventory-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('platform/cross-application-connectivity'),
                            ],
                            [
                                'title' => 'Advanced Data Transformation & Quality Engine',
//                                'description' => 'Optimize inventory decisions',
                                //'icon' => 'asset/icons/menu/inventory-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('platform/data-transformation-quality-engine'),
                            ],
                            [
                                'title' => 'Adaptive Infrastructure',
//                                'description' => 'Optimize inventory decisions',
                                // 'icon' => 'asset/icons/menu/inventory-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('platform/infrastructure'),
                            ],
                        ],
                    ],
                    [
                        'title' => 'Products',
                        'items' => [
                            [
                                'title' => 'Feed Management',
                                'description' => 'Streamline product distribution',
                                'icon' => 'asset/icons/menu/feed-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('products/feed-management'),
                            ],
                            [
                                'title' => 'Competitor Monitoring',
                                'description' => 'Track market dynamics',
                                'icon' => 'asset/icons/menu/market-sentry.svg',
                                'link' => $this->viewableEntityHelper->getUrl('products/competitor-monitoring'),
                            ],
                            [
                                'title' => 'Inventory Management',
                                'description' => 'Optimize inventory decisions',
                                'icon' => 'asset/icons/menu/inventory-management.svg',
                                'link' => $this->viewableEntityHelper->getUrl('products/inventory-management'),
                            ],
                            [
                                'title' => 'Integrations',
                                'description' => 'Connect business ecosystems',
                                'icon' => 'asset/icons/menu/integrations.svg',
                                'link' => $this->viewableEntityHelper->getUrl('products/integrations'),
                            ],
                            [
                                'title' => 'Automations',
                                'description' => 'Simplify workflow processes',
                                'icon' => 'asset/icons/menu/automations.svg',
                                'link' => $this->viewableEntityHelper->getUrl('products/automations'),
                            ],
                        ],
                    ],
//                    [
//                        'title' => 'Features',
//                        'items' => [
//                            [
//                                'title' => 'Integrate',
//                                'description' => 'Connect your platforms with ease and manage your data in one place so you can focus on growing your business.',
//                                'icon' => 'asset/icons/menu/integrate.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('products/integrate'),
//                            ],
//                            [
//                                'title' => 'Automate',
//                                'description' => 'Automate your processes. Gain better insights and reduce mistakes.',
//                                'icon' => 'asset/icons/menu/automate.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('products/automate'),
//                            ],
//                            [
//                                'title' => $this->localeHelper->translate('Visualize'),
//                                'description' => 'Monitor platforms, processes and data, identify issues and solve issues instantly.',
//                                'icon' => 'asset/icons/menu/visualize.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('products/visualize'),
//                            ],
//                            [
//                                'title' => 'Why Attlaz',
//                                'description' => 'Giving your team superpowers',
//                                'icon' => 'asset/icons/menu/why-attlaz.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('platform'),
//                            ],
//                            //                            [
//                            //                                'title'       => 'How it works',
//                            //                                'description' => 'Attlaz powers the pathways to accelerate your business.',
//                            //                                'icon'        => $this->resolver->getFrontendFileUrl('asset/icons/menu/how-it-works.svg',
//                            //                                'link'        => $this->resolver->getPageUrl('platform')
//                            //                            ],
//                        ],
//                    ],
                ],
            ],
            [
                'title' => 'Solutions',
                'type' => 'dropdown',
                'subsections' => [
                    [
                        'title' => 'Function',
                        // TODO: make these items come from the use-case repository
                        'items' => [
                            [
                                'title' => 'Ecommerce',
                                'description' => 'Enhance your ecommerce',
                                'icon' => 'asset/icons/use-cases/ecommerce.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/ecommerce'),
                            ],
                            [
                                'title' => 'Finance',
                                'description' => 'Streamline all your financial data',
                                'icon' => 'asset/icons/use-cases/finance.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/finance'),
                            ],
                            [
                                'title' => 'IT',
                                'description' => 'Centralize all your data and application integrations',
                                'icon' => 'asset/icons/use-cases/it.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/it'),
                            ],
//
//                            [
//                                'title' => 'Data management',
//                                'description' => 'Manage your data',
//                                'icon' => $this->resolver->getFrontendFileImageUrl('asset/icons/menu/data-migration.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('use-cases/data-management'),
//                            ],
                            [
                                'title' => 'Marketing',
                                'description' => 'Enable marketing automation',
                                'icon' => 'asset/icons/use-cases/marketing.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/marketing'),
                            ],
                            [
                                'title' => 'Sales',
                                'description' => 'Connect all your sales processes',
                                'icon' => 'asset/icons/use-cases/sales.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/sales'),
                            ],
                            //                            ['title' => 'API reference', 'link' => $this->resolver->getPageUrl('api-reference')],
                            //                            ['title' => 'Platform status', 'link' => $this->getConfiguration()->getValue('status_url'), 'target' => '_blank', 'rel' => 'noopener'],
                        ],
                    ],
                    [
                        'title' => 'By use case',
                        // TODO: make these items come from the use-case repository
                        'items' => [
                            [
                                'title' => 'Digital Marketing',
//                                'description' => 'Enhance your ecommerce',
                                //   'icon' => 'asset/icons/menu/ecommerce.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/digital-marketing'),
                            ],
                            [
                                'title' => 'Ecommerce Marketing',
//                                'description' => 'Streamline all your financial data',
                                //   'icon' => 'asset/icons/menu/finance.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/ecommerce-marketing'),
                            ],
//                            [
//                                'title' => 'Expense Management',
////                                'description' => 'Centralize all your data and application integrations',
//                                //   'icon' => 'asset/icons/menu/it.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('use-cases/expense-management'),
//                            ],
//
//                            [
//                                'title' => 'Financial Planning & Analysis',
////                                'description' => 'Enable marketing automation',
//                                //'icon' => 'asset/icons/menu/marketing.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('use-cases/financial-planning'),
//                            ],
                            [
                                'title' => 'Product Data Management',
//                                'description' => 'Connect all your sales processes',
                                // 'icon' => 'asset/icons/menu/sales.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/product-data-management'),
                            ],
//                            [
//                                'title' => 'Product Management',
////                                'description' => 'Connect all your sales processes',
//                                //    'icon' => 'asset/icons/menu/sales.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('use-cases/product-management'),
//                            ],
//                            [
//                                'title' => 'Product Operations',
////                                'description' => 'Connect all your sales processes',
//                                // 'icon' => 'asset/icons/menu/sales.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('use-cases/product-operations'),
//                            ],
//                            [
//                                'title' => 'Product Usage',
////                                'description' => 'Connect all your sales processes',
//                                // 'icon' => 'asset/icons/menu/sales.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('use-cases/product-usage'),
//                            ],
                            [
                                'title' => 'Inventory Management',
//                                'description' => 'Connect all your sales processes',
                                //  'icon' => 'asset/icons/menu/sales.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/inventory-management'),
                            ],
                            [
                                'title' => 'Sales Channel Management',
//                                'description' => 'Connect all your sales processes',
                                // 'icon' => 'asset/icons/menu/sales.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/sales-channel-management'),
                            ],
                            [
                                'title' => 'AI',
//                                'description' => 'Connect all your sales processes',
                                // 'icon' => 'asset/icons/menu/sales.svg',
                                'link' => $this->viewableEntityHelper->getUrl('use-cases/ai'),
                            ],
                        ],
                    ],
                    [
                        'title' => 'By application',
                        'items' => [
                            [
                                'title' => 'Magento',
                                'description' => '',
                                'icon' => 'asset/icons/adapter/magento-icon.svg',
                                'link' => $this->viewableEntityHelper->getUrl(PlatformDefinition::generateId('magento')),
                            ],
                            [
                                'title' => 'Adobe Commerce',
                                'description' => '',
                                'icon' => 'asset/icons/adapter/adobe-commerce-icon.svg',
                                'link' => $this->viewableEntityHelper->getUrl(PlatformDefinition::generateId('adobe-commerce')),
                            ],
                            [
                                'title' => 'Shopware',
                                'description' => '',
                                'icon' => 'asset/icons/adapter/shopware-icon.svg',
                                'link' => $this->viewableEntityHelper->getUrl(PlatformDefinition::generateId('shopware')),
                            ],
                            [
                                'title' => 'Shopify',
                                'description' => '',
                                'icon' => 'asset/icons/adapter/shopify-icon.svg',
                                'link' => $this->viewableEntityHelper->getUrl(PlatformDefinition::generateId('shopify')),
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
//            [
//                'title' => 'About',
//                'type' => 'dropdown',
//                'subsections' => [
//                    [
//                        'title' => 'About Attlaz',
//                        'items' => [
//                            [
//                                'title' => 'About',
//                                'description' => 'Discover what Attlaz is all about',
//                                'icon' => 'asset/icons/menu/about.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('about'),
//                            ],
//                            [
//                                'title' => 'Careers',
//                                'description' => 'Work with purpose. Leave your mark.',
//                                'icon' => 'asset/icons/menu/careers.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('careers'),
//                            ],
//                            [
//                                'title' => 'Partners',
//                                'description' => 'Learn how Attlaz partners are accelerating businesses',
//                                'icon' => 'asset/icons/menu/partners.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('partners'),
//                            ],
//                        ],
//                    ],
//                ],
//            ],
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
                    [
                        'title' => 'Services',
                        'items' => [
                            [
                                'title' => 'Customer Experience',
                                'description' => 'See how ' . $this->localeHelper->translate('organizations') . ' are using the Attlaz platform to achieve their goals.',
//                                'icon' => 'asset/icons/menu/customers.svg',
                                'link' => $this->viewableEntityHelper->getUrl('case-studies'),
                            ],
                            [
                                'title' => 'Partners',
                                'description' => 'Learn how Attlaz partners are accelerating businesses',
//                                'icon' => 'asset/icons/menu/partners.svg',
                                'link' => $this->viewableEntityHelper->getUrl('partners'),
                            ],
//                            [
//                                'title' => 'Consultancy',
////                                'description' => 'See how ' . $this->localeHelper->translate('organizations') . ' are using the Attlaz platform to achieve their goals.',
////                                'icon' => 'asset/icons/menu/customers.svg',
//                                'link' => $this->viewableEntityHelper->getUrl('case-studies'),
//                            ],
                        ],
                    ],
                    [
                        'title' => 'Support',
                        'items' => [
                            [
                                'title' => 'Documentation',
                                'description' => 'Access Attlaz documentation',
//                                'icon' => 'asset/icons/menu/documentation.svg',
                                'link' => 'https://docs.attlaz.com',
                                'target' => '_blank',
                                'rel' => 'noopener',
                            ],
                            [
                                'title' => 'Help center',
                                'description' => 'We are here to help you',
//                                'icon' => 'asset/icons/menu/support.svg',
                                'link' => $this->viewableEntityHelper->getUrl('support'),
                            ],
                        ],
                    ],
                    [
                        'title' => 'Explore',
                        'items' => [
                            [
                                'title' => 'Blog',
                                'description' => 'Read up on all things integrating, automating, and much more',
//                                'icon' => 'asset/icons/menu/blog.svg',
                                'link' => $this->viewableEntityHelper->getUrl('blog'),
                            ],
                            [
                                'title' => 'Customer stories',
                                'description' => 'See how ' . $this->localeHelper->translate('organizations') . ' are using the Attlaz platform to achieve their goals.',
//                                'icon' => 'asset/icons/menu/customers.svg',
                                'link' => $this->viewableEntityHelper->getUrl('case-studies'),
                            ],


                        ],
                    ],
                ],
            ],

//            [
//                'title' => 'Support',
//                'type' => 'dropdown',
//                'subsections' => [
//                    [
//                        'title' => 'Developer platform',
//                        'items' => [
//
//                        ],
//                    ],
//                ],
//            ],
            //            [
            //                'title' => 'Plans',
            //                'type'  => 'link',
            //                'link'  => $this->resolver->getPageUrl('plans')
            //            ]
        ];
    }

}
