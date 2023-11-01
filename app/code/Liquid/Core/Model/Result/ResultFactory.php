<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Result;

use DI\FactoryInterface;

readonly class ResultFactory
{
    public function __construct(private FactoryInterface $container)
    {

    }

    /**
     * @template T of Result - T
     * @param class-string<T> $type
     * @return T
     */
    public function create(string $type): Result
    {
        // TODO: check type
        return $this->container->make($type);
    }
}
