<?php

declare(strict_types=1);

namespace Liquid\Blog\Repository;

use Attlaz\Adapter\Base\RemoteService\SqlRemoteService;
use Liquid\Blog\Helper\ReadingTime;
use Liquid\Blog\Model\CategoryDefinition;
use Liquid\Blog\Model\PostDefinition;
use Liquid\Blog\Model\TagDefinition;
use Liquid\Content\Helper\LocaleHelper;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\Resource\PageSitemapPriority;
use Liquid\Core\Repository\BaseRepository;
use Liquid\Core\Repository\ViewableEntityRepository;

class BlogRepository extends BaseRepository implements ViewableEntityRepository
{
    /** @var PostDefinition[] */
    private array $posts = [];
    /** @var CategoryDefinition[] */
    private array $categories = [];
    /** @var TagDefinition[] */
    private array $tags = [];

    /**
     * @var PageDefinition[]
     */
    private array $pages = [];

    public function __construct(SqlRemoteService $remoteService, private readonly TemplateHelper $templateHelper, LocaleHelper $localeHelper)
    {
        parent::__construct($remoteService, $localeHelper);

        $this->pages = [
            PageDefinition::generate('blog', [
                'url_key' => 'blog',
                //            'template'      => 'Liquid_Blog::blog.phtml',
                'doc_css_class' => 'theme-aqua',
                'seo_title' => 'Attlaz blog with latest news and best practices',
                'seo_description' => 'Discover the latest trends in data connectivity, automation and other Attlaz insights.',
                'seo_keywords' => '',
                'priority' => PageSitemapPriority::BASE,
            ]),
            //            PageDefinition::generate('resources', [
            //                'url_key'       => 'resources',
            //                //            'template'      => 'Liquid_Blog::blog.phtml',
            //                'doc_css_class' => 'theme-aqua',
            //                'seo_title'         => 'Resources Hub',
            //                'seo_description' => 'Discover the latest trends in data connectivity, automation and other Attlaz insights.',
            //                'seo_keywords'  => '',
            //                'priority'      => PageSitemapPriority::BASE
            //            ])
        ];
        $this->posts = [
            PostDefinition::generate('black-friday-2022-get-your-business-ready', [
                'url_key' => 'black-friday-2022-get-your-business-ready',
                'template' => 'Liquid_Blog::post/page/black-friday-2022-get-your-business-ready.phtml',
                'seo_title' => "Are you ready for this year's Black Friday?",
                'seo_description' => "For ecommerce platforms, Black Friday is the day of the year with the most traffic and the run-up to Christmas sales, make sur eyou are well-prepared.",
                'intro' => "For ecommerce platforms, Black Friday is the day of the year with the most traffic and the run-up to Christmas sales. Being well-prepared is essential for your business. Next to profits, it is also about the reputation of your business. A well-thought-out strategy can differentiate your success from failure.",
                'seo_keywords' => '',
                'published' => '2022-11-14 15:16:17',
                'modified' => '2022-11-14 15:16:17',
                'image' => 'image/blog/black-friday-2022.jpg',
                'category' => 'insights',
                'tags' => ['attlaz-news'],
            ]),
            PostDefinition::generate('how-digitization-can-help-your-business', [
                'url_key' => 'how-digitization-can-help-your-business',
                'template' => 'Liquid_Blog::post/page/why-data-connectivity.phtml',
                'seo_title' => 'How digitization can help your business',
                'seo_description' => 'How digitization can help your business.',
                'intro' => "In today's world, data is the crux of major business decisions used by " . $localeHelper->translate('organizations') . " all over the world. As such, it is imperative that the organizations have access to the right data and be able to " . $localeHelper->translate('analyse') . " and make business decisions proactively.",
                'seo_keywords' => '',
                'published' => '2021-12-01 15:16:17',
                'modified' => '2021-12-01 15:16:17',
                'image' => 'image/blog/how-digitization-can-help-your-business.jpg',
                'category' => 'insights',
                'tags' => ['attlaz-news'],
            ]),
            PostDefinition::generate('why-data-connectivity', [
                'url_key' => 'why-data-connectivity',
                'template' => 'Liquid_Blog::post/page/why-data-connectivity.phtml',
                'seo_title' => 'Why data connectivity matters',
                'seo_description' => 'Data connectivity allows businesses to drive relevant, personalised interactions with audiences everywhere.',
                'intro' => "In today's world, data is the crux of major business decisions used by " . $localeHelper->translate('organizations') . " all over the world. As such, it is imperative that the organizations have access to the right data and be able to " . $localeHelper->translate('analyse') . " and make business decisions proactively.",
                'seo_keywords' => '',
                'published' => '2021-12-01 15:16:17',
                'modified' => '2021-12-01 15:16:17',
                'image' => 'image/blog/why-data-connectivity.jpg',
                'category' => 'insights',
                'tags' => ['attlaz-news'],
            ]),
            PostDefinition::generate('ecommerce-automation', [
                'url_key' => 'ecommerce-automation',
                'template' => 'Liquid_Blog::post/page/ecommerce-automation.phtml',
                'seo_title' => 'eCommerce Automation',
                'seo_description' => 'eCommerce Automation',
                'intro' => "Ecommerce Automation",
                'seo_keywords' => '',
                'published' => '2024-01-12 15:16:17',
                'modified' => '2024-01-12 15:16:17',
                'image' => 'image/blog/why-data-connectivity.jpg',
                'category' => 'insights',
                'tags' => ['attlaz-news'],
            ]),
            PostDefinition::generate('march2022-new-storage-engine', [
                'url_key' => 'march2022-new-storage-engine',
                'template' => 'Liquid_Blog::post/page/march2022.phtml',
                'seo_title' => 'New storage engine',
                'seo_description' => 'New storage engine + deprecation and end-of-life support policies',
                'intro' => 'In our March update we talk about the new storage engine, the deprecation and end-of-life support policies.',
                'seo_keywords' => '',
                'published' => '2022-03-30 19:01:13',
                'modified' => '2022-03-30 19:01:13',
                'image' => 'image/blog/march2022-new-storage-engine.jpg',
                'category' => 'product',
                'tags' => ['attlaz-news'],
            ]),
            PostDefinition::generate('improved-attlaz-status-page', [
                'url_key' => 'improved-attlaz-status-page',
                'template' => 'Liquid_Blog::post/page/improved-status-page.phtml',
                'seo_title' => 'Introducing the improved Attlaz status page',
                'seo_description' => "Introducing the improved Attlaz status page",
                'intro' => 'All Systems Operational!” is the kind of thing everyone loves to see and hear. Unfortunately failure is always lurking around the corner. During our outages in the past, we lacked a public facing way to inform our users and customers about potential issues on the platform, both for open source and private projects.',
                'seo_keywords' => '',
                'published' => '2022-04-06 09:37:18',
                'modified' => '2022-04-06 09:37:18',
                'image' => 'image/blog/improved-attlaz-status-page.jpg',
                'category' => 'product',
                'tags' => ['attlaz-news'],
            ]),
            //            PostDefinition::generate('getting-smart', [
            //                'url_key'      => 'getting-smart',
            //                'template'     => 'Liquid_Blog::post/page/getting-smart.phtml',
            //                'seo_title'        => 'Getting smart',
            //                'description'  => 'Attlaz is getting smarter',
            //                'intro'        => 'Attlaz is getting smarter',
            //                'seo_keywords' => '',
            //                'published'    => '2029-04-30 09:37:18',
            //                'modified'     => '2022-04-30 09:37:18',
            //                'image'        => 'image/blog/why-data-connectivity.png',
            //                'categories'   => ['product']
            //            ]),
            //            PostDefinition::generate('how-to-get-notified', [
            //                'url_key'      => 'how-to-get-notified',
            //                'template'     => 'Liquid_Blog::post/page/how-to-get-notified.phtml',
            //                'seo_title'        => 'Use our smart notification system',
            //                'description'  => 'Use our smart notification system',
            //                'intro'        => 'Use our smart notification system',
            //                'seo_keywords' => '',
            //                'published'    => '2029-04-30 09:37:18',
            //                'modified'     => '2022-04-30 09:37:18',
            //                'image'        => 'image/blog/why-data-connectivity.png',
            //                'categories'   => ['guides']
            //            ]),
            //            PostDefinition::generate('send-magento-order-to-erp', [
            //                'url_key'      => 'send-magento-order-to-erp',
            //                'template'     => 'Liquid_Blog::post/page/send-magento-order-information.phtml',
            //                'seo_title'        => 'How to send Magento order information to your ERP',
            //                'intro'        => 'How to send Magento order information to your ERP',
            //                'description'  => "How to send Magento order information to your ERP",
            //                'seo_keywords' => '',
            //                'published'    => '2029-05-18 09:37:18',
            //                'modified'     => '2022-04-18 09:37:18',
            //                'image'        => 'image/blog/why-data-connectivity.png',
            //                'categories'   => ['guides']
            //            ]),
            //            PostDefinition::generate('getting-started', [
            //                'url_key'      => 'getting-started',
            //                'template'     => 'Liquid_Blog::post/page/getting-started.phtml',
            //                'seo_title'        => 'How to get started with Attlaz',
            //                'intro'        => 'How to get started with Attlaz',
            //                'description'  => 'How to get started with Attlaz',
            //                'seo_keywords' => '',
            //                'published'    => '2029-05-18 09:37:18',
            //                'modified'     => '2022-04-18 09:37:18',
            //                'image'        => 'image/blog/why-data-connectivity.png',
            //                'categories'   => ['guides']
            //            ])
        ];
        $this->categories = [
            CategoryDefinition::generate('insights', [
                'url_key' => 'insights',
                'template' => 'Liquid_Blog::category/view.phtml',
                'seo_title' => 'Insights',
                'title_long' => 'Attlaz curated insights, interests and technical opinions. View the latest today',
                'seo_description' => 'Stay up-to-date with the most important new developments in software, integration, and automation.',
                'seo_keywords' => '',
            ]),
            CategoryDefinition::generate('product', [
                'url_key' => 'product',
                'template' => 'Liquid_Blog::category/view.phtml',
                'seo_title' => 'Product',
                'title_long' => 'What’s new with the Attlaz Platform',
                'seo_description' => 'Here are the latest updates for the Attlaz Platform - learn how to become a better integration and automated workflow builder today.',
                'seo_keywords' => '',
            ]),
            CategoryDefinition::generate('guides', [
                'url_key' => 'guides',
                'template' => 'Liquid_Blog::category/view.phtml',
                'seo_title' => 'Guides',
                'title_long' => 'Learn how to integrate & automate on the Attlaz Platform',
                'seo_description' => 'Use the Attlaz Platform to connect and automate your tech stack.',
                'seo_keywords' => '',
            ]),
            //            CategoryDefinition::generate('events', [
            //                'url_key'      => 'how-to',
            //                'template'     => 'Liquid_Blog::category/view.phtml',
            //                'seo_title'        => 'Events',
            //                'title_long'   => 'All our events in one place.',
            //                'description'  => 'Check out our past and future events, including in-person, live webinars and collaborative workshops with our team and partners.',
            //                'seo_keywords' => '',
            //            ]),
        ];

        $this->tags = [
            TagDefinition::generate('attlaz-news', [
                'url_key' => 'attlaz-news',
                'template' => 'Liquid_Blog::category/view.phtml',
                'seo_title' => 'Attlaz News',
                'seo_description' => 'Articles & Resources tagged Attlaz News',
                'seo_keywords' => '',
                'title_long' => 'Articles & Resources tagged Attlaz News',
            ]),
        ];

    }


    public function getByUrlKey(string $urlKey): PostDefinition|null
    {
        //        $rawArticle = $this->remoteService->fetchOne('SELECT * FROM docs_article WHERE path = ?', [$path]);
        //        if (!\is_null($rawArticle)) {
        //            return $this->parse($rawArticle);
        //        }

        foreach ($this->posts as $post) {

            if ($post->urlKey === $urlKey) {
                return $post;
            }
        }

        return null;
    }

    public function getCategoryById(string $id): CategoryDefinition|null
    {
        foreach ($this->categories as $category) {
            if ($category->id === $id) {
                return $category;
            }
        }
        return null;
    }

    public function getCategoryByUrlKey(string $urlKey): CategoryDefinition|null
    {
        foreach ($this->categories as $category) {
            if ($category->urlKey === $urlKey) {
                return $category;
            }
        }
        return null;
    }

    /**
     * @return CategoryDefinition[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getTagByUrlKey(string $urlKey): TagDefinition|null
    {
        foreach ($this->tags as $tag) {
            if ($tag->urlKey === $urlKey) {
                return $tag;
            }
        }
        return null;
    }

    public function getTagById(string $urlKey): TagDefinition|null
    {
        foreach ($this->tags as $tag) {
            if ($tag->id === $urlKey) {
                return $tag;
            }
        }
        return null;
    }

    /**
     * @return TagDefinition[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @return PostDefinition[]
     */
    public function getPosts(): array
    {

        $result = [];
        foreach ($this->posts as $post) {
            // if ($post->publishDate !== null && $post->publishDate < new \DateTime('now')) {
            $result[] = $post;
            //}
        }
        /**
         * Sort by publish date (newest first)
         */
        usort($result, static function (PostDefinition $a, PostDefinition $b) {
            if ($a->publishDate === $b->publishDate) {
                return 0;
            }
            return ($a->publishDate > $b->publishDate) ? -1 : 1;
        });


        return $result;
    }

    /**
     * @param string $categoryId
     * @return PostDefinition[]
     */
    public function getPostsByCategoryId(string $categoryId, int $limit = 100): array
    {
        $result = [];
        foreach ($this->getPosts() as $post) {
            if ($post->categoryId === $categoryId) {
                $result[] = $post;
            }
            if (count($result) >= $limit) {
                return $result;
            }
        }

        return $result;

    }

    /**
     * @param string $tagId
     * @return PostDefinition[]
     */
    public function getPostsByTagId(string $tagId): array
    {
        $result = [];
        foreach ($this->getPosts() as $post) {
            if (in_array($tagId, $post->tagIds, true)) {
                $result[] = $post;
            }
        }

        return $result;

    }

    public function getPageById(string $id): PageDefinition|null
    {
        foreach ($this->pages as $page) {
            if ($page->id === $id) {
                return $page;
            }
        }
        return null;
    }

    public function getPages(): array
    {
        return $this->pages;
    }

    public function estimateReadingTime(PageDefinition $post): int
    {
        $templateContent = $this->templateHelper->getTemplateFileContent($post->template);
        //        $templateContent = HtmlHelper::removeHtml($templateContent);
        return ReadingTime::estimateReadingTime($templateContent);
    }

    public function getEntities(): array
    {
        return array_merge($this->pages, $this->posts, $this->categories);
    }
}
