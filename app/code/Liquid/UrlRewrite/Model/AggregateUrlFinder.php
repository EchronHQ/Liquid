<?php
declare(strict_types=1);

namespace Liquid\UrlRewrite\Model;

use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\UrlRewrite\Model\Resource\UrlRewrite;

class AggregateUrlFinder implements UrlFinderInterface
{
    /**
     * @param array{class:string} $children
     */
    public function __construct(
        private readonly array                  $children,
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }


    public function findOneByRequestPath(string $requestPath, SegmentId $segmentId): UrlRewrite|null
    {
        foreach ($this->children as $child) {
            /** @var UrlFinderInterface $urlFinder */
            $urlFinder = $this->objectManager->get($child['class']);
            $rewrite = $urlFinder->findOneByRequestPath($requestPath, $segmentId);
            if ($rewrite !== null) {
                return $rewrite;
            }
        }
        return null;
    }

    public function findAllByRequestPath(string $requestPath, SegmentId $segmentId): array
    {
        $result = [];
        foreach ($this->children as $child) {
            /** @var UrlFinderInterface $urlFinder */
            $urlFinder = $this->objectManager->get($child['class']);
            $rewrites = $urlFinder->findAllByRequestPath($requestPath, $segmentId);
            $result = array_merge($result, $rewrites);
        }
        return $result;
    }
}
