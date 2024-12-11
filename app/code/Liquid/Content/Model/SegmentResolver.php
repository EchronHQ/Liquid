<?php
declare(strict_types=1);

namespace Liquid\Content\Model;

use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class SegmentResolver
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {
    }
//    /**
//     * {@inheritdoc}
//     * @return ScopeDefault
//     */
//    public function getScope($scopeId = null)
//    {
//        if (!$this->defaultScope) {
//            $this->defaultScope = $this->objectManager->create(ScopeDefault::class);
//        }
//
//        return $this->defaultScope;
//    }
    public function getCurrentSegmentId(): SegmentId
    {
        return new SegmentId('seg_0');
    }
}
