<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout\Generator;

use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class GeneratorContextFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return GeneratorContext
     */
    public function create(array $data = []): GeneratorContext
    {
        return $this->objectManager->create(GeneratorContext::class, $data);
    }
}
