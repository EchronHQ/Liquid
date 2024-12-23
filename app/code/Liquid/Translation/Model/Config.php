<?php
declare(strict_types=1);

namespace Liquid\Translation\Model;

use Liquid\Content\Model\Segment\SegmentGroupId;
use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Framework\App\Config\ScopeConfig;

class Config
{
    public function __construct(
        private readonly ScopeConfig $config,
    )
    {

    }

    public function isDebugActive(SegmentGroupId|SegmentId|null $segmentId = null): bool
    {
        return $this->config->isSetFlag('dev/translate_debug/active', $segmentId);
    }
}
