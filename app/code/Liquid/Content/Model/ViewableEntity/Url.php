<?php
declare(strict_types=1);

namespace Liquid\Content\Model\ViewableEntity;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\UrlRewrite\Model\Resource\UrlRewrite;
use Liquid\UrlRewrite\Model\UrlFinderInterface;

class Url
{
    public function __construct(
        private readonly UrlFinderInterface    $urlFinder,
        private readonly \Liquid\Framework\Url $url,
    )
    {

    }

    /**
     * Retrieve Entity URL
     *
     * @param AbstractViewableEntity $entity
     * @param bool $useSid forced SID mode
     * @return string
     */
    public function getEntityUrl(AbstractViewableEntity $entity, bool|null $useSid = null): string
    {
        $params = [];
        if (!$useSid) {
            $params['_nosid'] = true;
        }
        return $this->getUrl($entity, $params);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param AbstractViewableEntity $entity
     * @param array $params
     * @return string
     * @throws \Exception
     */
    public function getUrl(AbstractViewableEntity $entity, array $params = []): string
    {
        $routePath = '';
        $routeParams = $params;

        $segmentId = null;// $entity->getStoreId();


//        if ($entity->hasUrlDataObject()) {
//            $requestPath = $entity->getUrlDataObject()->getUrlRewrite();
//            $routeParams['_scope'] = $entity->getUrlDataObject()->getStoreId();
//        } else {
        $requestPath = $entity->getRequestPath();
        if (empty($requestPath) && $requestPath !== false) {
            $filterData = [
                UrlRewrite::ENTITY_ID => $entity->id,
                // TODO: this should be a property in the entity
                UrlRewrite::ENTITY_TYPE => get_class($entity),
                UrlRewrite::SEGMENT_ID => $segmentId,
                UrlRewrite::REDIRECT_TYPE => 0,
            ];
//                $useCategories = $this->scopeConfig->getValue(
//                    \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY,
//                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
//                );

//                $filterData[UrlRewrite::METADATA]['category_id']
//                    = $categoryId && $useCategories ? $categoryId : '';

            $rewrite = $this->urlFinder->findOneByData($filterData);

            if ($rewrite !== null) {
                $requestPath = $rewrite->getRequestPath();
                $entity->setRequestPath($requestPath);
            } else {
                $entity->setRequestPath(null);
            }
        }
        // }

//        if (isset($routeParams['_scope'])) {
//            $storeId = $this->storeManager->getStore($routeParams['_scope'])->getId();
//        }
//
//        if ($storeId != $this->storeManager->getStore()->getId()) {
//            $routeParams['_scope_to_url'] = true;
//        }

        if (!empty($requestPath)) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routePath = $entity->getViewRoute();
            $routeParams['id'] = $entity->id;
//            $routeParams['s'] = $entity->getUrlPath();
//            if ($categoryId) {
//                $routeParams['category'] = $categoryId;
//            }
        }

        // reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

//        $url = $this->urlFactory->create()->setScope($storeId);
        return $this->url->getUrl($routePath, $routeParams);
    }
}
