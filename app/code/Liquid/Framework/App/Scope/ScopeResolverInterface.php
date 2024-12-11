<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Scope;

interface ScopeResolverInterface
{
    /**
     * Retrieve application scope object
     */
    public function getScope(ScopeId|null $scopeId = null): ScopeInterface;

    /**
     * Retrieve scopes array
     *
     * @return ScopeInterface[]
     */
    public function getScopes(): array;
}
