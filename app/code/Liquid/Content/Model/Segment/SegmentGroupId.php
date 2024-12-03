<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Segment;

class SegmentGroupId implements \Stringable
{
    public function __construct(private readonly string $id)
    {

    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(SegmentGroupId $id): bool
    {
        return $this->id === $id->id;
    }
}
