<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager;

use Psr\Container\ContainerInterface;

interface ObjectManagerInterface extends ContainerInterface
{
    /**
     * Create new object instance
     *
     * @template T
     *
     * @param class-string<T> $type
     * @param array $arguments
     * @return T
     */
    public function create(string $type, array $arguments = []): mixed;

    /**
     * Retrieve cached object instance
     *
     * @template T
     *
     * @param class-string<T> $type
     * @return T
     */
    public function get(string $type): mixed;

    /**
     * Configure object manager
     *
     * @param array $configuration
     * @return void
     */
    public function configure(array $configuration): void;

    /**
     * Returns if the object manager knows a type or not
     * @param string $type
     * @return bool
     */
    public function has(string $type): bool;
}
