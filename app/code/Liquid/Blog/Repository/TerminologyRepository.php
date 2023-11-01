<?php

declare(strict_types=1);

namespace Liquid\Blog\Repository;

use Liquid\Blog\Model\TermDefinition;
use Liquid\Content\Model\Resource\PageSitemapPriority;

class TerminologyRepository
{
    /** @var TermDefinition[] */
    private array $terms;

    public function __construct()
    {
        /**
         * multi-channel
         * data masking
         *
         */

        /**
         * Knowledge
         * - Data Harmonization
         * - ETL
         * - ROAS
         */
        $this->terms = [
            /**
             * https://www.matillion.com/what-is-etl-the-ultimate-guide/
             */
            TermDefinition::generate('etl', [
                'url_key' => 'etl',
                'template' => 'Liquid_Blog::term/page/etl.phtml',
                'term' => 'ETL',
                'term_long' => 'Extract, transform and load (ETL)',
                'seo_title' => 'What is ETL?',
                'description' => 'What is ETL? The ultimate guide, definition and more',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],

            ]),
            TermDefinition::generate('wms', [
                'url_key' => 'wms',
                'template' => 'Liquid_Blog::term/page/wms.phtml',
                'term' => 'WMS',
                'term_long' => 'Warehouse Management System (WMS)',
                'seo_title' => 'What is WMS?',
                'description' => 'What is WMS? The ultimate guide, definition and more',
                'seo_keywords' => '',
                'modified' => '2022-10-30 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],

            ]),
            TermDefinition::generate('erp', [
                'url_key' => 'erp',
                'template' => 'Liquid_Blog::term/page/erp.phtml',
                'term' => 'ERP',
                'term_long' => 'Enterprise Resource Planning (ERP)',
                'seo_title' => 'What is ERP?',
                'description' => 'What is ERP? The ultimate guide, definition and more',
                'seo_keywords' => '',
                'modified' => '2022-11-05 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => ['erp'],

            ]),
            TermDefinition::generate('crm', [
                'url_key' => 'crm',
                'template' => 'Liquid_Blog::term/page/crm.phtml',
                'term' => 'CRM',
                'term_long' => 'Customer Relationship Management (CRM)',
                'seo_title' => 'What is CRM?',
                'description' => 'What is CRM? The ultimate guide, definition and more',
                'seo_keywords' => '',
                'modified' => '2022-11-18 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => ['crm'],

            ]),
            TermDefinition::generate('pim', [
                'url_key' => 'pim',
                'template' => 'Liquid_Blog::term/page/pim.phtml',
                'term' => 'PIM',
                'term_long' => 'Product Information Management (PIM)',
                'seo_title' => 'What is PIM?',
                'description' => 'What is PIM?',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => ['pim'],

            ]),
            TermDefinition::generate('data-silos', [
                'url_key' => 'data-silos',
                'template' => 'Liquid_Blog::term/page/data-silos.phtml',
                'term' => 'Data Silos',
                'term_long' => 'Data Silos',
                'seo_title' => 'What are data silos?',
                'description' => 'What are data silos?',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],
            ]),
            TermDefinition::generate('data-analysis', [
                'url_key' => 'data-analysis',
                'template' => 'Liquid_Blog::term/page/data-analysis.phtml',
                'term' => 'Data Analysis',
                'term_long' => 'Data Analysis',
                'seo_title' => 'What is data analysis?',
                'description' => 'What is data analysis?',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],
            ]),
            TermDefinition::generate('fulfillment', [
                'url_key' => 'fulfillment',
                'template' => 'Liquid_Blog::term/page/fulfillment.phtml',
                'term' => 'Fulfillment',
                'term_long' => 'Fulfillment',
                'seo_title' => 'What is fulfillment?',
                'description' => 'What is fulfillment?',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],
            ]),
            TermDefinition::generate('ipaas', [
                'url_key' => 'ipaas',
                'template' => 'Liquid_Blog::term/page/ipaas.phtml',
                'term' => 'iPaaS',
                'term_long' => 'Integration Platform as a Service (iPaaS)',
                'seo_title' => 'What is iPaaS?',
                'description' => 'What is iPaaS?',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],
            ]),
            TermDefinition::generate('dirty-data', [
                'url_key' => 'dirty-data',
                'template' => 'Liquid_Blog::term/page/dirty-data.phtml',
                'term' => 'Dirty data',
                'term_long' => 'Dirty data',
                'seo_title' => 'What is dirty data?',
                'description' => 'What is dirty data?',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],
            ]),
            TermDefinition::generate('data-management', [
                'url_key' => 'data-management',
                'template' => 'Liquid_Blog::term/page/data-management.phtml',
                'term' => 'Data management',
                'term_long' => 'Data management',
                'seo_title' => 'What is data management?',
                'description' => 'What is data management?',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],
            ]),
            TermDefinition::generate('data integration techniques', [
                'url_key' => 'data integration techniques',
                'template' => 'Liquid_Blog::term/page/data-integration-techniques.phtml',
                'term' => 'Data integration techniques',
                'term_long' => 'Data integration techniques',
                'seo_title' => 'What are data integration techniques?',
                'description' => 'What are data integration techniques?',
                'seo_keywords' => '',
                'modified' => '2022-10-23 15:49:06',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],
            ]),
            TermDefinition::generate('customer data platform', [
                'url_key' => 'customer data platform',
                'template' => 'Liquid_Blog::term/page/customer-data-platform.phtml',
                'term' => 'CDP',
                'term_long' => 'Customer data platform (CDP)',
                'seo_title' => 'What is a customer data platform?',
                'description' => 'What is a customer data platform?',
                'seo_keywords' => '',
                'modified' => '2023-09-01 13:16:00',
                'priority' => PageSitemapPriority::LOW,
                'use_case_categories' => [],
            ]),
        ];
    }


    public function getByUrlKey(string $urlKey): TermDefinition|null
    {
        foreach ($this->terms as $term) {
            if ($term->urlKey === $urlKey) {
                return $term;
            }
        }
        return null;
    }

    /**
     * @return TermDefinition[]
     */
    public function getAll(): array
    {
        return $this->terms;
    }
}
