<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Segment;

use Liquid\Content\Model\Segment\SegmentId;

class SegmentCodeResolver
{
    private array $resolvedScopeCodes = [];

    public function resolve(SegmentId $segmentId): string
    {
        if (isset($this->resolvedScopeCodes['segment'][$segmentId->__toString()])) {
            return $this->resolvedScopeCodes['segment'][$segmentId->__toString()];
        }

        // TODO: further implement this
        $resolverScopeCode = $segmentId->__toString();

        $this->resolvedScopeCodes['segment'][$segmentId->__toString()] = $resolverScopeCode;

        return $resolverScopeCode;
    }
}
