<?php
declare(strict_types=1);

namespace Liquid\UrlRewrite\Model\Storage;

use Liquid\Content\Model\Segment\SegmentId;
use Liquid\UrlRewrite\Model\Resource\UrlRewrite;
use Liquid\UrlRewrite\Model\UrlFinderInterface;

class DbStorage implements UrlFinderInterface
{
    public const TABLE_NAME = 'url_rewrite';

    /**
     * @inheritdoc
     */
    public function findOneByRequestPath(string $requestPath, SegmentId $segmentId): UrlRewrite|null
    {
        // TODO: Implement findOneByRequestPath() method.
        return null;
    }

    /**
     * @inheritdoc
     */
    public function findAllByRequestPath(string $requestPath, SegmentId $segmentId): array
    {
        // TODO: Implement findAllByRequestPath() method.
        return [];
    }
}
