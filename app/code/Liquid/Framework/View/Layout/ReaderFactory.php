<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout;

use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Layout\Reader\ReaderInterface;

class ReaderFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    /**
     * Create reader instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @return ReaderInterface
     * @throws \InvalidArgumentException
     */
    public function create(string $className, array $data = []): ReaderInterface
    {
        $reader = $this->objectManager->create($className, $data);
        if (!$reader instanceof ReaderInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement ' . ReaderInterface::class
            );
        }
        return $reader;
    }
}
