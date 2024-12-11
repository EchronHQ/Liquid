<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Scope;

use Liquid\Content\Model\ScopeType;

interface ScopeInterface
{
    /**
     * Default scope reference code
     */
    public const ScopeType SCOPE_DEFAULT = ScopeType::DEFAULT;

    /**
     * Retrieve scope code
     */
    public function getCode(): string;

    /**
     * Get scope identifier
     */
    public function getId(): ScopeId;

    /**
     * Get scope type
     */
    public function getScopeType(): ScopeType;

    /**
     * Get scope type name
     */
    public function getScopeTypeName(): string;

    /**
     * Get scope name
     */
    public function getName(): string;
}
