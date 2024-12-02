<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Segment;

class SegmentResolver
{

    public function getCurrentSegmentId(): SegmentId
    {
        return new SegmentId('0');
    }
}
