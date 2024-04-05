<?php

declare(strict_types=1);

namespace Liquid\Core\Repository;

use Liquid\Content\Model\Resource\UrlRewrite;
use Liquid\Content\Model\Resource\UrlRewriteType;
use Liquid\Core\Helper\RequestRewriteHelper;

class UrlRepository extends BaseRepository
{
    /** @var UrlRewrite[] */
    private array $urlRewrites = [];
    private bool $urlRewritesLoaded = false;

    //    public function getAll(): array
    //    {
    //        $urls = $this->remoteService->fetchAll('SELECT * FROM url');
    //
    //        $result = [];
    //
    //        foreach ($urls as $url) {
    //            $result[] = $this->parse($url);
    //        }
    //        return $result;
    //    }

    //    public function getByRequest(string $request): Url|null
    //    {
    //        $url = $this->remoteService->fetchOne('SELECT * FROM url WHERE request = ?', [$request]);
    //        if (!\is_null($url)) {
    //            return $this->parse($url);
    //        }
    //
    //        return null;
    //    }


    private function loadRewrites(): void
    {
        $dbUrlRewrites = [
            // Pages
            //            new UrlRewrite('', 'page/view/home', UrlRewrite::PERMANENT, true),,
            //            new UrlRewrite('pricing', 'page/view/pricing', UrlRewrite::PERMANENT, true),,
            //            new UrlRewrite('platform', 'page/view/platform', UrlRewrite::PERMANENT, true),
            //            new UrlRewrite('contact', 'page/view/contact', UrlRewrite::PERMANENT, true),
            //            new UrlRewrite('about', 'page/view/about', UrlRewrite::PERMANENT, true),
            //            new UrlRewrite('environment', 'page/view/environment', UrlRewrite::PERMANENT, true),
            //            new UrlRewrite('legal/privacy', 'page/view/platform', UrlRewrite::PERMANENT, true),


            new UrlRewrite('/docs/adapters/magento2', 'https://docs.attlaz.com/platform/magento2/', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/adapters/magento2/attributes', 'https://docs.attlaz.com/platform/magento2/attributes/', UrlRewriteType::PERMANENT),

            new UrlRewrite('/docs/general/notifications', 'https://docs.attlaz.com/product/notifications/', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/flows', 'https://docs.attlaz.com/product/flows/', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/general/monitoring', 'https://docs.attlaz.com/product/monitoring/', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/api', 'https://docs.attlaz.com/api/', UrlRewriteType::PERMANENT),

            new UrlRewrite('/docs/connect', 'https://docs.attlaz.com/product/integrations/', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/adapters/overview', 'https://docs.attlaz.com/integrations/', UrlRewriteType::PERMANENT),

            new UrlRewrite('/docs', 'https://docs.attlaz.com', UrlRewriteType::PERMANENT),
            new UrlRewrite('/documentation', 'https://docs.attlaz.com', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/api', 'https://docs.attlaz.com/api/reference', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/:a', 'https://docs.attlaz.com', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/:a/:b', 'https://docs.attlaz.com', UrlRewriteType::PERMANENT),
            new UrlRewrite('/docs/:a/:b/:c', 'https://docs.attlaz.com', UrlRewriteType::PERMANENT),

            //            new UrlRewrite('/docs/flows', '', UrlRewriteType::PERMANENT),
            //            new UrlRewrite('/docs/flows', '', UrlRewriteType::PERMANENT),


            //            new UrlRewrite('/documentation', '/docs'),
            //            new UrlRewrite('/docs/flow', '/docs/development/flow'),
            //            new UrlRewrite('/docs/flows', '/docs/development/flow'),
            //            new UrlRewrite('/docs/configure-project', '/docs/general/overview'),

            new UrlRewrite('/product/adapters', '/platform'),
            new UrlRewrite('/product/reports', '/platform'),
            new UrlRewrite('/product/features', '/platform'),
            new UrlRewrite('/product/manage', '/platform'),


            //            new UrlRewrite('/pricing', '/plans'),
            //
            new UrlRewrite('/product/manage', '/platform', UrlRewriteType::TEMPORARY),
            new UrlRewrite('/product/reports', '/platform', UrlRewriteType::TEMPORARY),
            new UrlRewrite('/product/monitor', '/platform', UrlRewriteType::TEMPORARY),
            new UrlRewrite('/product/features', '/platform', UrlRewriteType::TEMPORARY),
            new UrlRewrite('/product/adapters', '/platform', UrlRewriteType::TEMPORARY),
            new UrlRewrite('/product/channels', '/platform', UrlRewriteType::TEMPORARY),
            //
            new UrlRewrite('/solutions/industry', '/platform', UrlRewriteType::TEMPORARY),
            new UrlRewrite('/solutions/industries', '/platform', UrlRewriteType::TEMPORARY),
            new UrlRewrite('/solutions/role', '/platform', UrlRewriteType::TEMPORARY),
            new UrlRewrite('/solutions/use-cases', '/platform', UrlRewriteType::TEMPORARY),
            //            new UrlRewrite('/blog', '/blog/blog/overview', UrlRewriteType::INTERNAL),
            //            new UrlRewrite('/blog/:post', '/blog/post/view/post/:post', UrlRewriteType::INTERNAL),
            //            new UrlRewrite('/blog/category/:category', '/blog/blog/category/category/:category', UrlRewriteType::INTERNAL),


            //            new UrlRewrite('/platforms', '/platforms/connector/overview', UrlRewrite::INTERNAL),

            new UrlRewrite('/connectors', '/platforms', UrlRewriteType::PERMANENT),
            new UrlRewrite('/connectors/:connector', '/platforms/:connector', UrlRewriteType::PERMANENT),
            new UrlRewrite('/connector/:connector', '/platforms/:connector', UrlRewriteType::PERMANENT),

            new UrlRewrite('/resources', '/blog', UrlRewriteType::PERMANENT),
            new UrlRewrite('/resources/:postId', '/blog/:postId', UrlRewriteType::PERMANENT),
            new UrlRewrite('/resources/category/:categoryId', '/blog/category/:categoryId', UrlRewriteType::PERMANENT),
            new UrlRewrite('/resources/term/:termId', '/blog/term/:termId', UrlRewriteType::PERMANENT),

            //            new UrlRewrite('/connectors/:connector', '/connector/connector/view/connector/:connector', UrlRewrite::INTERNAL),
            new UrlRewrite('/connectors/category/:connectorCategory', '/platforms/category/:connectorCategory', UrlRewriteType::PERMANENT),
            new UrlRewrite('/connector/category/:connectorCategory', '/platforms/category/:connectorCategory', UrlRewriteType::PERMANENT),
            new UrlRewrite('/connectors/type/:connectorType', '/connector/type/view/type/:connectorType', UrlRewriteType::PERMANENT),
            new UrlRewrite('/connector/type/:connectorType', '/platforms/type/:connectorCategory', UrlRewriteType::PERMANENT),
            new UrlRewrite('/platforms/category/e-commerce', '/platforms/category/ecommerce', UrlRewriteType::PERMANENT),


            new UrlRewrite('/use-cases/e-commerce', '/use-cases/ecommerce', UrlRewriteType::PERMANENT),
            new UrlRewrite('/use-cases/data-migration', '/use-cases/data-management', UrlRewriteType::PERMANENT),
            new UrlRewrite('/products/visualise', '/products/visualize', UrlRewriteType::PERMANENT),

            new UrlRewrite('/connect/:hmm', '/platforms', UrlRewriteType::PERMANENT),

        ];

        $this->urlRewrites = \array_merge($this->urlRewrites, $dbUrlRewrites);

        $this->urlRewritesLoaded = true;

    }

    public static function escapeId(string $input): string
    {
        return \str_replace(['/', '//'], '_', $input);
    }

    final public function addRewrite(UrlRewrite $rewrite): void
    {
        if (!$this->urlRewritesLoaded) {
            $this->loadRewrites();
        }
        $this->urlRewrites[] = $rewrite;
    }

    /**
     * @param string $urlPath
     * @return UrlRewrite|null
     */
    public function getRewrite(string $urlPath): UrlRewrite|null
    {
        if (!$this->urlRewritesLoaded) {
            $this->loadRewrites();
        }
        foreach ($this->urlRewrites as $urlRewrite) {
            $rewrite = RequestRewriteHelper::rewrite($urlRewrite, $urlPath);
            if ($rewrite !== null) {
                return $rewrite;
            }
        }
        return null;
    }

//    private function parse(array $raw): Url
//    {
//        return new Url($raw['request'], $raw['target'], $raw['entity_type'], $raw['entity_id']);
//    }
}
