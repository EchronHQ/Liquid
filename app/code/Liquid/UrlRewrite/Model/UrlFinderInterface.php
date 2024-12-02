<?php
declare(strict_types=1);

namespace Liquid\UrlRewrite\Model;

use Liquid\Content\Model\Segment\SegmentId;
use Liquid\UrlRewrite\Model\Resource\UrlRewrite;

interface UrlFinderInterface
{
    /**
     * Find rewrite by specific data
     *
     * @param string $requestPath
     * @param SegmentId $segmentId
     * @return UrlRewrite|null
     */
    public function findOneByRequestPath(string $requestPath, SegmentId $segmentId): UrlRewrite|null;

    /**
     * Find rewrites by specific data
     *
     * @param string $requestPath
     * @param SegmentId $segmentId
     * @return UrlRewrite[]
     */
    public function findAllByRequestPath(string $requestPath, SegmentId $segmentId): array;
}
