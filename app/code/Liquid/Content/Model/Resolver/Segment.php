<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Resolver;

use Liquid\Content\Model\Segment\SegmentManager;
use Liquid\Framework\App\Scope\ScopeId;
use Liquid\Framework\App\Scope\ScopeInterface;
use Liquid\Framework\App\Scope\ScopeResolverInterface;

class Segment implements ScopeResolverInterface
{
    public function __construct(
        private readonly SegmentManager $segmentManager
    )
    {

    }

    public function getScope(ScopeId|null $segmentId = null): ScopeInterface
    {
        $scope = $this->segmentManager->getSegment($segmentId);
        if (!$scope instanceof ScopeInterface) {
            throw new \Exception('The scope object is invalid. Verify the scope object and try again.');
        }
        return $scope;
    }

    public function getScopes(): array
    {
        return $this->segmentManager->getSegments();
    }
}
