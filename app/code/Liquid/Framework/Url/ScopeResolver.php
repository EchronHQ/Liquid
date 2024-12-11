<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;

use Liquid\Framework\App\Area\AreaCode;
use Liquid\Framework\App\Scope\ScopeId;
use Liquid\Framework\App\Scope\ScopeInterface;

class ScopeResolver implements ScopeResolverInterface
{
    public function __construct(
        private readonly \Liquid\Framework\App\Scope\ScopeResolverInterface $scopeResolver,
        private readonly AreaCode|null                                      $areaCode = null
    )
    {

    }

    public function getAreaCode(): AreaCode|null
    {
        return $this->areaCode;
    }

    public function getScope(ScopeId|null $scopeId = null): ScopeInterface
    {
        $scope = $this->scopeResolver->getScope($scopeId);
        if (!$scope instanceof \Liquid\Framework\Url\ScopeInterface) {
            throw new \Exception('The scope object is invalid. Verify the scope object and try again.');
        }
        return $scope;
    }

    public function getScopes(): array
    {
        return $this->scopeResolver->getScopes();
    }
}
