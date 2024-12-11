<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Scope;

class ScopeCodeResolver
{
    private array $resolvedScopeCodes = [];

    public function resolve(ScopeId $scopeId): string
    {
        if (isset($this->resolvedScopeCodes['scope'][$scopeId->__toString()])) {
            return $this->resolvedScopeCodes['scope'][$scopeId->__toString()];
        }

        // TODO: further implement this
        $resolverScopeCode = $scopeId->__toString();

        $this->resolvedScopeCodes['scope'][$scopeId->__toString()] = $resolverScopeCode;

        return $resolverScopeCode;
    }
}
