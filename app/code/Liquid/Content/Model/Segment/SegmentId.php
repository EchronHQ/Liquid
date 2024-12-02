<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Segment;

class SegmentId implements \Stringable
{
    public function __construct(private readonly string $id)
    {

    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(SegmentId $id): bool
    {
        return $this->id === $id->id;
    }
}
