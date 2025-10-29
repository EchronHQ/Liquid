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

    /**
     * @inheritDoc
     */
    public function getValue(string $path, ScopeId|null $scopeId = null): mixed
    {
        if ($scopeId === null) {
            $configPath = 'default';
        } elseif ($scopeId instanceof SegmentId) {
            $configPath = ScopeType::SEGMENT->value;
        } else {
            throw new \Exception('Unknown config scope');
        }
        if ($scopeId !== null) {
            $scopeCode = $this->scopeCodeResolver->resolve($scopeId);
            if ($scopeCode === null) {
                // TODO: log this (should we throw error?)
                throw new \Exception('Segment code not found');
            }

//                if ($scopeId instanceof ScopeId) {
//                    // TODO: add segment type to path
//                }
            $configPath .= '/' . $scopeCode;

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

    /**
     * @inheritDoc
     */
    public function isSetFlag(string $path, ScopeId|null $scopeId = null): bool
    {
        return (bool)$this->getValue($path, $scopeId);
    }

    /**
     * Retrieve configuration.
     *
     * ('modules') - modules status configuration data
     * ('scopes', 'websites/base') - base website data
     * ('scopes', 'stores/default') - default store data
     *
     * ('system', 'default/web/seo/use_rewrites') - default system configuration data
     * ('system', 'websites/base/web/seo/use_rewrites') - 'base' website system configuration data
     *
     * ('i18n', 'default/en_US') - translations for default store and 'en_US' locale
     *
     * @param string $configType
     * @param string $path
     * @param array|int|string|bool|null $default
     * @return mixed
     */
    public function get(string $configType, string $path = '', array|int|string|bool|null $default = null): mixed
    {
        $result = null;

        if (isset($this->types[$configType])) {
            $result = $this->types[$configType]->get($path);
        }

        return $result ?? $default;
    }
}
