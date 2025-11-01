<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller;

use DI\DependencyException;
use DI\FactoryInterface;
use DI\NotFoundException;
use Liquid\Framework\Exception\ContextException;

readonly class ResultFactory
{
    public function __construct(private FactoryInterface $container)
    {

    }

    /**
     * @template T of ResultInterface - T
     * @param class-string<T> $type
     * @return T
     * @throws ContextException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function create(string $type): ResultInterface
    {
        // TODO: validate type (check if match with known types)

        $resultInstance = $this->container->make($type);
        if (!\is_a($resultInstance, ResultInterface::class)) {
            throw new ContextException(\get_class($resultInstance) . ' is not instance of ResultInterface');
        }
        return $resultInstance;
    }
}
