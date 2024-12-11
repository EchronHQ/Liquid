<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;

use Liquid\Content\Model\ScopeType;
use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Framework\App\Scope\ScopeCodeResolver;
use Liquid\Framework\App\Scope\ScopeId;

class ScopeConfig implements SegmentConfigInterface
{
    public function __construct(
        private readonly ScopeCodeResolver $scopeCodeResolver,
        /** @var ConfigTypeInterface[] */
        private readonly array             $types = []
    )
    {
    }

    public function getValue(string $path, ScopeId|null $scopeId = null): mixed
    {
        if ($scopeId === null) {
            $configPath = 'default';
        } else if ($scopeId instanceof SegmentId) {
            $configPath = ScopeType::SEGMENT->value;
        } else {
            throw new \Exception('Uknown config scope');
        }
        if ($scopeId !== null) {
            $scopeCode = $this->scopeCodeResolver->resolve($scopeId);
            if ($scopeCode === null) {
                // TODO: log this (should we throw error?)
                throw new \Exception('Segment code not found');
            } else {
//                if ($scopeId instanceof ScopeId) {
//                    // TODO: add segment type to path
//                }
                $configPath .= '/' . $scopeCode;
            }

        }
        if ($path) {
            $configPath .= '/' . $path;
        }
        return $this->get('system', $configPath);
    }

    public function getBoolValue(string $path, ScopeId|null $scopeId = null): bool
    {
        $value = $this->getValue($path, $scopeId);
        return $value === true;
    }

    public function isSetFlag(string $path, ScopeId|null $scopeId = null): bool
    {
        return (bool)$this->getValue($path, $scopeId);
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
