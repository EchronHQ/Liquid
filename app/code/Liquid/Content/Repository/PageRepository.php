<?php

declare(strict_types=1);

namespace Liquid\Content\Repository;

use Liquid\Content\Helper\LocaleHelper;
use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\Resource\PageSitemapPriority;
use Liquid\Core\Helper\IdHelper;
use Liquid\Core\Repository\BaseRepository;
use Liquid\Core\Repository\ViewableEntityRepository;
use Liquid\Framework\Database\Sql\SqlFactory;

class PageRepository extends BaseRepository implements ViewableEntityRepository
{
    /** @var PageDefinition[] */
    private array $pages;


    public function __construct(SqlFactory $sqlFactory, LocaleHelper $localeHelper)
    {
        parent::__construct($sqlFactory, $localeHelper);

        $this->pages = [
            PageDefinition::generate('home', [
                'url_key' => '',
                'template' => 'Liquid_Content::page/home.phtml',
                'doc_css_class' => 'theme--light palette--wintergreen accent--green',
                // TODO: improve SEO tags
                'seo_title' => 'One platform to streamline all workflows',
                'seo_description' => 'Attlaz is the platform to streamline all workflows with integrations, automation and visualisation',
                'seo_keywords' => AbstractViewableEntity::DEFAULT_KEYWORDS,
                'priority' => PageSitemapPriority::HIGHEST,
                'modified' => '2022-10-23 15:49:06',
                'urlRewrites' => [''],
            ]),
            PageDefinition::generate('plans', [
                'url_key' => 'plans',
                'template' => 'Liquid_Content::page/plans.phtml',
                'doc_css_class' => 'theme--light palette--wintergreen accent--green',
                'seo_title' => 'Pricing plans and editions',
                'seo_description' => 'See what Attlaz plan fits to your needs',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2023-05-03 11:32:00',
                'urlRewrites' => ['plans'],
            ]),

            PageDefinition::generate('platform', [
                'url_key' => 'platform',
                'template' => 'Liquid_Content::page/platform.phtml',
                'doc_css_class' => 'theme--light palette--pomegranate accent--purple',
                'seo_title' => 'Discover the Attlaz platform',
                'seo_description' => 'Learn how Attlaz improve your internal data management',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
                'urlRewrites' => ['platform'],

            ]),
            PageDefinition::generate('why-attlaz', [
                'url_key' => 'why-attlaz',
                'template' => 'Liquid_Content::page/why-attlaz.phtml',
                'doc_css_class' => 'theme--light palette--pomegranate accent--purple',
                'seo_title' => 'Discover the Attlaz platform',
                'seo_description' => 'Learn how Attlaz improve your internal data management',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2024-07-02 15:49:06',
                'urlRewrites' => ['why-attlaz'],

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
                'template' => 'Liquid_Content::page/contact.phtml',
                'doc_css_class' => 'contact theme--light palette--chroma accent--cyan',
                'seo_title' => 'Get in touch with us',
                'seo_description' => "Contact us for support or if you're interested in learning more about which Attlaz products meet your needs.",
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
                'urlRewrites' => ['contact'],
            ]),
            PageDefinition::generate('demo', [
                'url_key' => 'demo',
                'template' => 'Liquid_Content::page/demo.phtml',
                'doc_css_class' => 'demo theme--light palette--chroma accent--cyan',
                'seo_title' => 'Request a demo or schedule a meeting',
                'seo_description' => "Book a demo if you're interested in learning more about how Attlaz can improve your organization.",
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
                'urlRewrites' => ['demo'],
            ]),


            PageDefinition::generate('about', [
                'url_key' => 'about',
                'template' => 'Liquid_Content::page/about.phtml',
                'doc_css_class' => 'theme--light palette--chroma accent--cyan',
                'seo_title' => 'Our company',
                'seo_description' => "Attlaz is on a mission to make any platform connect and make all data available across your organization",
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
                'urlRewrites' => ['about'],
            ]),
            PageDefinition::generate('partners', [
                'url_key' => 'partners',
                'template' => 'Liquid_Content::page/partners.phtml',
                'doc_css_class' => 'theme--light palette--chroma accent--cyan',
                'seo_title' => 'Find our become an Attlaz partner',
                'seo_description' => "Partner with the world’s leading system integrators and technology innovators",
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
                'urlRewrites' => ['partners'],
            ]),
            PageDefinition::generate('careers', [
                'url_key' => 'careers',
                'template' => 'Liquid_Content::page/careers.phtml',
                'doc_css_class' => 'theme--light palette--wintergreen accent--green',
                'seo_title' => 'Careers - Join our fast growing team',
                'seo_description' => 'Careers - Join our fast growing team',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::MEDIUM,
                'modified' => '2022-10-15 15:49:06',
                'urlRewrites' => ['careers'],
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
                'template' => 'Liquid_Content::page/environment.phtml',
                'doc_css_class' => 'theme--light palette--lemonlime accent--green',
                'seo_title' => 'Environment and sustainability',
                'seo_description' => 'Attlaz empower companies to improve their carbon footprint and be more sustainable',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::IGNORE,
                'urlRewrites' => ['environment'],
            ]),
            PageDefinition::generate('support', [
                'url_key' => 'support',
                'template' => 'Liquid_Content::page/support.phtml',
                'doc_css_class' => 'theme--light palette--chroma accent--cyan',
                'seo_title' => 'Support Center',
                'seo_description' => 'Everything you need to know right here at your fingertips. Ask questions, browse around for answers, or submit your feature requests.',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::LOW,
                'modified' => '2022-10-10 15:49:06',
                'urlRewrites' => ['support'],
            ]),
            //            Page::generate('partners', ['template' => 'page/partners.phtml', 'doc_css_class' => 'docs']),

            PageDefinition::generate('legal/privacy', [
                'url_key' => 'legal/privacy',
                'template' => 'Liquid_Content::page/legal/privacy.phtml',
                'doc_css_class' => 'legal theme--light palette--chroma accent--cyan',
                'seo_title' => 'Privacy Policy',
                'seo_description' => 'This privacy policy has been compiled to better serve those who are concerned with how their ‘Personally Identifiable Information’ is being used online.',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::LOW,
                'modified' => '2022-10-10 15:49:06',
                'urlRewrites' => ['legal/privacy'],
            ]),
            PageDefinition::generate('legal/cookies', [
                'url_key' => 'legal/cookies',
                'template' => 'Liquid_Content::page/legal/cookies.phtml',
                'doc_css_class' => 'legal theme--light palette--chroma accent--cyan',
                'seo_title' => 'Cookie Policy',
                'seo_description' => 'Learn everything about how we manage cookies',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::LOW,
                'urlRewrites' => ['legal/cookies'],
            ]),
            //            Page::generate('manage-cookies', ['template' => 'page/legal/manage-cookies.phtml', 'doc_css_class' => 'legal header-dark', 'seo_title' => 'Manage Cookies']),
            PageDefinition::generate('legal/terms', [
                'url_key' => 'legal/terms',
                'template' => 'Liquid_Content::page/legal/terms.phtml',
                'doc_css_class' => 'legal theme--light palette--chroma accent--cyan',
                'seo_title' => 'Terms and Conditions',
                'seo_description' => 'These terms and conditions (the “contract”) are a legally obligatory agreement between the “User” who receives the services described in this document and Attlaz.',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::LOW,
                'modified' => '2022-10-10 15:49:06',
                'urlRewrites' => ['legal/terms'],
            ]),
            /**
             * Products
             */
            PageDefinition::generate('products/integrate', [
                'url_key' => 'products/integrate',
                'template' => 'Liquid_Content::page/products/integrate.phtml',
                'doc_css_class' => 'theme--light palette--pomegranate accent--purple',
                'seo_title' => 'Integrations made easy',
                'seo_description' => 'Integrations made easy',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
                'urlRewrites' => ['products/integrate'],
            ]),
            PageDefinition::generate('products/automate', [
                'url_key' => 'products/automate',
                'template' => 'Liquid_Content::page/products/automate.phtml',
                'doc_css_class' => 'theme--light palette--pomegranate accent--purple',
                'seo_title' => 'Powerful automation',
                'seo_description' => 'Powerful automation',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
                'urlRewrites' => ['products/automate'],
            ]),
            PageDefinition::generate('products/visualize', [
                'url_key' => 'products/visualize',
                'template' => 'Liquid_Content::page/products/visualize.phtml',
                'doc_css_class' => 'theme--light palette--pomegranate accent--purple',
                'seo_title' => 'Visualization and monitoring',
                'seo_description' => 'Visualization and monitoring',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
                'urlRewrites' => ['products/visualize'],
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
             * Industries
             */
            // TODO: make overview page with all industries
            PageDefinition::generate('industries/startups', [
                'url_key' => 'industries/startups',
                'template' => 'Liquid_Content::page/industries/startups.phtml',
                'doc_css_class' => 'theme--light palette--chroma accent--cyan',
                'seo_title' => 'Attlaz for startups',
                'seo_description' => 'Attlaz for startups',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::IGNORE,
                'modified' => '2023-11-17 12:41:03',
                'urlRewrites' => ['industries/startups'],
            ]),
            /**
             * Case studies
             */

            PageDefinition::generate('case-studies', [
                'url_key' => 'case-studies',
                'template' => 'Liquid_Content::page/case-studies.phtml',
                'doc_css_class' => 'theme--light palette--overcast accent--blue',
                'seo_title' => 'Case studies',
                'seo_description' => 'Case studies',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::HIGH,
                'modified' => '2022-10-23 15:49:06',
                'urlRewrites' => ['case-studies'],
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
            PageDefinition::generate('not-found', [
                'url_key' => 'not-found',
                'template' => 'Liquid_Content::page/notfound.phtml',
                'doc_css_class' => 'theme--light palette--overcast accent--blue',
                'seo_title' => 'Page not found',
                'seo_description' => 'Page not found',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::IGNORE,
                'modified' => '2022-10-01 15:49:06',
                'urlRewrites' => ['not-found'],
            ]),
        ];

    }

    public function getById(int|string $id): PageDefinition|null
    {
        foreach ($this->pages as $page) {
            $escapedId = IdHelper::escapeId($page->id);
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
     * @inheritDoc
     */
    public function getEntities(): array
    {
        return $this->getAll();
    }

    /**
     * @return PageDefinition[]
     */
    public function getAll(): array
    {
        return $this->pages;
    }
}
