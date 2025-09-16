<?php
declare(strict_types=1);

namespace Liquid\UrlRewrite\Model;

use Liquid\UrlRewrite\Model\Resource\UrlRewrite;

interface UrlFinderInterface
{

    /**
     * Find rewrite by specific data
     *
     * @param array $data
     * @return UrlRewrite|null
     */
    public function findOneByData(array $data): UrlRewrite|null;

    /**
     * Find rewrites by specific data
     *
     * @param array $data
     * @return UrlRewrite[]
     */
    public function findAllByData(array $data): array;
}
