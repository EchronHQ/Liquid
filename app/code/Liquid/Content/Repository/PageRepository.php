<?php

declare(strict_types=1);

namespace Liquid\Content\Repository;

use Attlaz\Adapter\Base\RemoteService\SqlRemoteService;
use Liquid\Content\Helper\LocaleHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\Resource\PageSitemapPriority;
use Liquid\Core\Repository\BaseRepository;
use Liquid\Core\Repository\UrlRepository;
use Liquid\Core\Repository\ViewableEntityRepository;
use Liquid\Core\Router;

class PageRepository extends BaseRepository implements ViewableEntityRepository
{
    /** @var PageDefinition[] */
    private array $pages;


    public function __construct(SqlRemoteService $remoteService, LocaleHelper $localeHelper)
    {
        parent::__construct($remoteService, $localeHelper);

        $this->pages = [
            PageDefinition::generate('home', [
                'url_key' => '',
                'template' => 'page/home.phtml',
                'doc_css_class' => 'theme-aqua',
                // TODO: improve SEO tags
                'seo_title' => 'One platform to streamline all workflows',
                'seo_description' => 'Attlaz is the platform to streamline all workflows with integrations, automation and visualisation',
                'seo_keywords' => PageDefinition::DEFAULT_KEYWORDS,
                'priority' => PageSitemapPriority::HIGHEST,
                'modified' => '2022-10-23 15:49:06',
            ]),
            PageDefinition::generate('plans', [
                'url_key' => 'plans',
                'template' => 'page/plans.phtml',
                'doc_css_class' => 'header-dark',
                'seo_title' => 'Pricing plans and editions',
                'seo_description' => 'See what Attlaz plan fits to your needs',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2023-05-03 11:32:00',
            ]),

            PageDefinition::generate('platform', [
                'url_key' => 'platform',
                'template' => 'page/platform.phtml',
                'doc_css_class' => 'platform',
                'seo_title' => 'Discover the Attlaz platform',
                'seo_description' => 'Learn how Attlaz improve your internal data management',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',

            ]),

            //            Page::generate('solutions-use-case', ['template' => 'page/solutions/use-case.phtml', 'doc_css_class' => 'features']),
            //            Page::generate('solutions-role', ['template' => 'page/solutions/role.phtml', 'doc_css_class' => 'features']),
            //            Page::generate('solutions-industry', ['template' => 'page/solutions/industry.phtml', 'doc_css_class' => 'features']),

            //            Page::generate('product-features', ['template' => 'page/product/features.phtml', 'doc_css_class' => 'features']),
            //            Page::generate('product-adapters', ['template' => 'page/product/adapters.phtml', 'doc_css_class' => 'features']),
            //            Page::generate('product-manage', ['template' => 'page/product/manage.phtml', 'doc_css_class' => 'features']),
            //            Page::generate('product-monitor', ['template' => 'page/product/monitor.phtml', 'doc_css_class' => 'features']),
            //            Page::generate('product-channels', ['template' => 'page/product/channels.phtml', 'doc_css_class' => 'features']),
            //            Page::generate('product-reports', ['template' => 'page/product/reports.phtml', 'doc_css_class' => 'features']),

            PageDefinition::generate('contact', [
                'url_key' => 'contact',
                'template' => 'page/contact.phtml',
                'doc_css_class' => 'contact',
                'seo_title' => 'Get in touch with us',
                'seo_description' => "Contact us for support or if you're interested in learning more about which Attlaz products meet your needs.",
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
            ]),
            PageDefinition::generate('demo', [
                'url_key' => 'demo',
                'template' => 'page/demo.phtml',
                'doc_css_class' => 'demo',
                'seo_title' => 'Request a demo or schedule a meeting',
                'seo_description' => "Book a demo if you're interested in learning more about how Attlaz can improve your organisation.",
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
            ]),


            PageDefinition::generate('about', [
                'url_key' => 'about',
                'template' => 'page/about.phtml',
                'doc_css_class' => '',
                'seo_title' => 'Our company',
                'seo_description' => "Attlaz is on a mission to make any platform connect and make all data available across your organisation",
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
            ]),
            PageDefinition::generate('partners', [
                'url_key' => 'partners',
                'template' => 'page/partners.phtml',
                'doc_css_class' => 'theme-aqua',
                'seo_title' => 'Find our become an Attlaz partner',
                'seo_description' => "Partner with the world’s leading system integrators and technology innovators",
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
            ]),
            PageDefinition::generate('careers', [
                'url_key' => 'careers',
                'template' => 'page/careers.phtml',
                'doc_css_class' => '',
                'seo_title' => 'Careers - Join our fast growing team',
                'seo_description' => 'Careers - Join our fast growing team',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
            ]),
            //            PageDefinition::generate('slack', [
            //                'url_key'       => 'integrations/slack',
            //                'template'      => 'page/integration/slack.phtml',
            //                'doc_css_class' => '',
            //                'seo_title'         => 'Slack',
            //                'seo_description' => 'Slack',
            //                'seo_keywords'  => '',
            //            ]),

            PageDefinition::generate('environment', [
                'url_key' => 'environment',
                'path' => 'environment',
                'template' => 'page/environment.phtml',
                'seo_title' => 'Environment and sustainability',
                'seo_description' => 'Attlaz empower companies to improve their carbon footprint and be more sustainable',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::IGNORE,
            ]),
            PageDefinition::generate('support', [
                'url_key' => 'support',
                'template' => 'page/support.phtml',
                'doc_css_class' => 'header-light support',
                'seo_title' => 'Support Center',
                'seo_description' => 'Everything you need to know right here at your fingertips. Ask questions, browse around for answers, or submit your feature requests.',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::LOW,
                'modified' => '2022-10-10 15:49:06',
            ]),
            //            Page::generate('partners', ['template' => 'page/partners.phtml', 'doc_css_class' => 'docs']),

            PageDefinition::generate('legal/privacy', [
                'url_key' => 'legal/privacy',
                'template' => 'page/legal/privacy.phtml',
                'doc_css_class' => 'legal header-dark',
                'seo_title' => 'Privacy Policy',
                'seo_description' => 'This privacy policy has been compiled to better serve those who are concerned with how their ‘Personally Identifiable Information’ is being used online.',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::LOW,
                'modified' => '2022-10-10 15:49:06',
            ]),
            PageDefinition::generate('legal/cookies', [
                'url_key' => 'legal/cookies',
                'template' => 'page/legal/cookies.phtml',
                'doc_css_class' => 'legal header-dark',
                'seo_title' => 'Cookie Policy',
                'seo_description' => 'Learn everything about how we manage cookies',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::LOW,
            ]),
            //            Page::generate('manage-cookies', ['template' => 'page/legal/manage-cookies.phtml', 'doc_css_class' => 'legal header-dark', 'seo_title' => 'Manage Cookies']),
            PageDefinition::generate('legal/terms', [
                'url_key' => 'legal/terms',
                'template' => 'page/legal/terms.phtml',
                'doc_css_class' => 'legal header-dark',
                'seo_title' => 'Terms and Conditions',
                'seo_description' => 'These terms and conditions (the “contract”) are a legally obligatory agreement between the “User” who receives the services described in this document and Attlaz.',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::LOW,
                'modified' => '2022-10-10 15:49:06',
            ]),
            /**
             * Products
             */
            PageDefinition::generate('products/integrate', [
                'url_key' => 'products/integrate',
                'template' => 'page/products/integrate.phtml',
                'doc_css_class' => 'theme-ocean',
                'seo_title' => 'Integrations made easy',
                'seo_description' => 'Integrations made easy',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
            ]),
            PageDefinition::generate('products/automate', [
                'url_key' => 'products/automate',
                'template' => 'page/products/automate.phtml',
                'doc_css_class' => 'theme-ocean',
                'seo_title' => 'Powerful automation',
                'seo_description' => 'Powerful automation',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
            ]),
            PageDefinition::generate('products/visualise', [
                'url_key' => 'products/visualise',
                'template' => 'page/products/visualise.phtml',
                'doc_css_class' => 'theme-ocean',
                'seo_title' => 'Visualisation and monitoring',
                'seo_description' => 'Visualisation and monitoring',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
            ]),
            /**
             * Solutions - Overview
             */
            //            PageDefinition::generate('solutions', [
            //                'url_key'       => 'solutions',
            //                'template'      => 'page/solutions/overview.phtml',
            //                'doc_css_class' => '',
            //                'seo_title'         => 'Attlaz solutions',
            //                'seo_description' => 'The digital solution for every business model',
            //                'seo_keywords'  => '',
            //            ]),
            /**
             * Solutions - Use cases
             */
            PageDefinition::generate('use-cases/data-migration', [
                'url_key' => 'use-cases/data-migration',
                'template' => 'page/use-cases/data-migration.phtml',
                'doc_css_class' => 'theme-aqua',
                'seo_title' => 'Attlaz use case: Data migration',
                'seo_description' => 'Attlaz use case: Data migration',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
            ]),
            PageDefinition::generate('use-cases/ecommerce', [
                'url_key' => 'use-cases/ecommerce',
                'template' => 'page/use-cases/ecommerce.phtml',
                'doc_css_class' => 'theme-aqua',
                'seo_title' => 'Attlaz use case: Ecommerce',
                'seo_description' => 'Attlaz use case: Ecommerce',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
            ]),
            PageDefinition::generate('use-cases/marketing', [
                'url_key' => 'use-cases/marketing',
                'template' => 'page/use-cases/marketing.phtml',
                'doc_css_class' => 'theme-aqua',
                'seo_title' => 'Attlaz use case: Marketing',
                'seo_description' => 'Attlaz use case: Marketing',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
            ]),
            PageDefinition::generate('use-cases/inventory', [
                'url_key' => 'use-cases/inventory',
                'template' => 'page/use-cases/inventory-management.phtml',
                'doc_css_class' => 'theme-aqua',
                'seo_title' => 'Attlaz use case: Inventory Management',
                'seo_description' => 'Attlaz use case: Inventory Management',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::IGNORE,
                'modified' => '2022-10-23 15:49:06',
            ]),


            PageDefinition::generate('case-studies', [
                'url_key' => 'case-studies',
                'template' => 'page/case-studies.phtml',
                'doc_css_class' => 'theme-ocean',
                'seo_title' => 'Case studies',
                'seo_description' => 'Case studies',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
            ]),
            /**
             * Solutions - Role
             */
            //            PageDefinition::generate('solutions/roles/business-owner', [
            //                'url_key'       => 'solutions/roles/business-owner',
            //                'template'      => 'page/solutions/roles/business-owner.phtml',
            //                'doc_css_class' => '',
            //                'seo_title'         => 'Solutions for business owners',
            //                'seo_description' => 'Solutions for business owners',
            //                'seo_keywords'  => '',
            //            ]),
            //            PageDefinition::generate('solutions/roles/architect', [
            //                'url_key'       => 'solutions/roles/architect',
            //                'template'      => 'page/solutions/roles/architect.phtml',
            //                'doc_css_class' => '',
            //                'seo_title'         => 'Solutions for architects',
            //                'seo_description' => 'Solutions for architects',
            //                'seo_keywords'  => '',
            //            ]),
            //            PageDefinition::generate('solutions/roles/developer', [
            //                'url_key'       => 'solutions/roles/developer',
            //                'template'      => 'page/solutions/roles/developer.phtml',
            //                'doc_css_class' => '',
            //                'seo_title'         => 'Solutions for developers',
            //                'seo_description' => 'Solutions for developers',
            //                'seo_keywords'  => '',
            //            ]),
            //            PageDefinition::generate('solutions/agency', [
            //                'url_key'       => 'solutions/agency',
            //                'template'      => 'page/solutions/roles/agency.phtml',
            //                'doc_css_class' => '',
            //                'title'         => 'Solutions for agencies',
            //                'seo_description' => 'Solutions for agencies',
            //                'seo_keywords'  => '',
            //            ]),
            /**
             * Connectors
             */


            /**
             * System
             */
            PageDefinition::generate(Router::PAGE_NOT_FOUND_IDENTIFIER, [
                'url_key' => Router::PAGE_NOT_FOUND_IDENTIFIER,
                'template' => 'page/notfound.phtml',
                'doc_css_class' => 'header-dark',
                'seo_title' => 'Page not found',
                'seo_description' => 'Page not found',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::IGNORE,
                'modified' => '2022-10-01 15:49:06',
            ]),
        ];

    }

    public function getById(int|string $id): PageDefinition|null
    {
        foreach ($this->pages as $page) {
            $escapedId = UrlRepository::escapeId($page->id);
            if ($escapedId === $id) {
                return $page;
            }
        }
        return null;
    }

//    public function getByPath(string $path): ?Page
//    {
//        foreach ($this->pages as $page) {
//            if ($page->path === $path) {
//                return $page;
//            }
//        }
//        return null;
//    }

    /**
     * @return PageDefinition[]
     */
    public function getAll(): array
    {
        return $this->pages;
    }

    public function getEntities(): array
    {
        return $this->getAll();
    }
}
