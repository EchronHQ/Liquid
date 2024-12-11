<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Segment;


use Liquid\Content\Model\SegmentRepository;
use Liquid\Content\Model\SegmentResolver;
use Liquid\Core\Helper\Profiler;
use Liquid\Framework\App\Config\ScopeConfig;

class SegmentManager
{
    private SegmentId|null $currentSegmentId = null;

    public function __construct(
        private readonly ScopeConfig       $scopeConfig,
        private readonly SegmentRepository $segmentRepository,
        private readonly SegmentResolver   $segmentResolver,
        private readonly Profiler          $profiler
    )
    {

    }

    /**
     * @return Segment[]
     */
    public function getAll(): array
    {
        return $this->segmentRepository->getList();
    }

    /**
     * Retrieve application segment object
     *
     * @param string|SegmentId|null $segmentId
     * @return Segment|null
     */
    public function getSegment(string|SegmentId|null $segmentId = null): Segment|null
    {
        if ($segmentId === null) {
            if (null === $this->currentSegmentId) {
                $this->profiler->profilerStart('segment.resolve');
                $this->currentSegmentId = $this->segmentResolver->getCurrentSegmentId();
                $this->profiler->profilerFinish('segment.resolve');
            }
            $segmentId = $this->currentSegmentId;
        }

        // TODO: what if we still don't find the segment? There should always be a default segment active
        return is_string($segmentId)
            ? $this->segmentRepository->getByCode($segmentId)
            : $this->segmentRepository->getById($segmentId);
    }

    /**
     * Retrieve segments array
     *
     * @param bool $withDefault
     * @param bool $codeKey
     * @return Segment[]
     */
    public function getSegments(bool $withDefault = false, bool $codeKey = false): array
    {
        $stores = [];
        foreach ($this->segmentRepository->getList() as $segment) {
            if (!$withDefault && $segment->getId() === new SegmentId('seg_0')) {
                continue;
            }
            if ($codeKey) {
                $stores[$segment->getCode()] = $segment;
            } else {
                $stores[$segment->getId()->__toString()] = $segment;
            }
        }
        return $stores;
    }
}
