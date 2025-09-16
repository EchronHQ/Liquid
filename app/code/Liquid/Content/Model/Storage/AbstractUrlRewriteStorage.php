<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Storage;

use Liquid\Core\Helper\RequestRewriteHelper;
use Liquid\UrlRewrite\Model\UrlFinderInterface;

abstract class AbstractUrlRewriteStorage implements UrlFinderInterface
{
    /** @inheritdoc */
    public function findOneByData(array $data): \Liquid\UrlRewrite\Model\Resource\UrlRewrite|null
    {
        $urlRewrites = $this->getUrlRewrites();

        if (isset($data[\Liquid\UrlRewrite\Model\Resource\UrlRewrite::REQUEST_PATH])) {
            foreach ($urlRewrites as $urlRewrite) {
                $rewrite = RequestRewriteHelper::rewrite($urlRewrite, $data[\Liquid\UrlRewrite\Model\Resource\UrlRewrite::REQUEST_PATH]);
                if ($rewrite !== null) {
                    return $rewrite;
                }
            }
            return null;
        }
        if (isset($data[\Liquid\UrlRewrite\Model\Resource\UrlRewrite::ENTITY_ID])) {
            foreach ($urlRewrites as $urlRewrite) {
                // TODO: make sure other fields are matching as well
                if ($urlRewrite->getEntityId() === $data[\Liquid\UrlRewrite\Model\Resource\UrlRewrite::ENTITY_ID]) {
                    return $urlRewrite;
                }
            }
            return null;
        }


        return null;
    }

    /** @inheritdoc */
    public function findAllByData(array $data): array
    {
        $urlRewrites = $this->getUrlRewrites();
        $result = [];

        if (isset($data[\Liquid\UrlRewrite\Model\Resource\UrlRewrite::REQUEST_PATH])) {
            foreach ($urlRewrites as $urlRewrite) {
                $rewrite = RequestRewriteHelper::rewrite($urlRewrite, $data[\Liquid\UrlRewrite\Model\Resource\UrlRewrite::REQUEST_PATH]);
                if ($rewrite !== null) {
                    $result[] = $rewrite;
                }
            }
        } else if (isset($data[\Liquid\UrlRewrite\Model\Resource\UrlRewrite::ENTITY_ID])) {
//            foreach ($urlRewrites as $urlRewrite) {
//                if($urlRewrite->)
//            }
        }


        return $result;
    }

    /**
     * @return \Liquid\UrlRewrite\Model\Resource\UrlRewrite[]
     * @throws \Exception
     */
    abstract protected function getUrlRewrites(): array;
}
