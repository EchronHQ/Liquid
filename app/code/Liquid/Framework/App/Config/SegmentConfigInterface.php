<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;


use Liquid\Content\Model\Segment\SegmentGroupId;
use Liquid\Framework\App\Scope\ScopeId;

interface SegmentConfigInterface
{
    /**
     * Default scope type
     */
    public const string SCOPE_TYPE_DEFAULT = 'default';

    /**
     * Retrieve config value by path and scope.
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param SegmentGroupId|ScopeId|null $scopeId
     * @return mixed
     */
    public function getValue(string $path, SegmentGroupId|ScopeId|null $scopeId = null): mixed;

    /**
     * Retrieve config flag by path and scope
     *
     * @param string $path The path through the tree of configuration values, e.g., 'general/store_information/name'
     * @param SegmentGroupId|ScopeId|null $scopeId
     * @return bool
     */
    public function isSetFlag(string $path, SegmentGroupId|ScopeId|null $scopeId = null): bool;
}
