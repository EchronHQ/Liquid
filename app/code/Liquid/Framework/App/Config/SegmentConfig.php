<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;

use Liquid\Content\Model\Segment\SegmentGroupId;
use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Framework\App\Segment\SegmentCodeResolver;

class SegmentConfig implements SegmentConfigInterface
{
    public function __construct(
        private readonly SegmentCodeResolver $segmentCodeResolver,
        /** @var ConfigTypeInterface[] */
        private readonly array               $types = []
    )
    {
    }

    public function getValue(string $path, SegmentGroupId|SegmentId|null $segmentId = null): mixed
    {
        $configPath = 'default';
        if ($segmentId !== null) {
            $segmentCode = $this->segmentCodeResolver->resolve($segmentId);
            if ($segmentCode === null) {
                // TODO: log this (should we throw error?)
                throw new \Exception('Segment code not found');
            } else {
                if ($segmentId instanceof SegmentId) {
                    // TODO: add segment type to path
                }
                $configPath = '/' . $segmentCode;
            }

        }
        if ($path) {
            $configPath .= '/' . $path;
        }
        return $this->get('system', $configPath);
    }

    public function getBoolValue(string $path, SegmentGroupId|SegmentId|null $segmentId = null): bool
    {
        $value = $this->getValue($path, $segmentId);
        return $value === true;
    }

    public function isSetFlag(string $path, SegmentGroupId|SegmentId|null $segmentId = null): bool
    {
        return (bool)$this->getValue($path, $segmentId);
    }

    public function get(string $configType, string $path = '', array|int|string|bool|null $default = null): mixed
    {
        $result = null;
        if (isset($this->types[$configType])) {
            $result = $this->types[$configType]->get($path);
        }

        return $result !== null ? $result : $default;
    }
}
