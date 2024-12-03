<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;


use Liquid\Content\Model\Segment\SegmentGroupId;
use Liquid\Content\Model\Segment\SegmentId;

interface SegmentConfigInterface
{
    /**
     * Default scope type
     */
    public const string SCOPE_TYPE_DEFAULT = 'default';

    /**
     * Retrieve config value by path and scope.
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param SegmentGroupId|SegmentId|null $segmentId
     * @return mixed
     */
    public function getValue(string $path, SegmentGroupId|SegmentId|null $segmentId = null): mixed;

    /**
     * Retrieve config flag by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param SegmentGroupId|SegmentId|null $segmentId
     * @return bool
     */
    public function isSetFlag(string $path, SegmentGroupId|SegmentId|null $segmentId = null): bool;
}
