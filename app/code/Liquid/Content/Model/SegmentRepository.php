<?php
declare(strict_types=1);

namespace Liquid\Content\Model;

use Liquid\Content\Model\Segment\Segment;
use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class SegmentRepository
{
    /** @var Segment[] */
    private array $segments = [];

    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {
        $segment = $this->objectManager->create(Segment::class);
        $segment->id = new SegmentId('0');
        $segment->code = 'default';
        $this->segments[] = $segment;
    }

    /**
     * @return Segment[]
     */
    public function getList(): array
    {
        return $this->segments;
    }

    /**
     * @param SegmentId $id
     * @return Segment|null
     */
    public function getById(SegmentId $id): Segment|null
    {
        foreach ($this->segments as $segment) {
            if ($segment->id->equals($id)) {
                return $segment;
            }
        }
        return null;
    }

    /**
     * @param string $code
     * @return Segment|null
     */
    public function getByCode(string $code): Segment|null
    {
        foreach ($this->segments as $segment) {
            if ($segment->code === $code) {
                return $segment;
            }
        }
        return null;
    }
}
