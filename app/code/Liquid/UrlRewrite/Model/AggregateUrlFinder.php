<?php
declare(strict_types=1);

namespace Liquid\UrlRewrite\Model;

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


    public function findOneByData(array $data): UrlRewrite|null
    {
        foreach ($this->children as $child) {
            /** @var UrlFinderInterface $urlFinder */
            $urlFinder = $this->objectManager->get($child['class']);
            $rewrite = $urlFinder->findOneByData($data);
            if ($rewrite !== null) {
                return $rewrite;
            }
        }
        return null;
    }

    public function findAllByData(array $data): array
    {
        $result = [];
        foreach ($this->children as $child) {
            /** @var UrlFinderInterface $urlFinder */
            $urlFinder = $this->objectManager->get($child['class']);
            $rewrites = $urlFinder->findAllByData($data);
            $result = [...$result, ...$rewrites];
        }
        return $result;
    }
}
