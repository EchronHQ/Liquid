<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Area;


use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class FrontNameResolverFactory
{

    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    /**
     * Create front name resolver
     *
     * @param string $className
     * @return FrontNameResolverInterface
     */
    public function create(string $className): FrontNameResolverInterface
    {
        return $this->objectManager->create($className);
    }
}
