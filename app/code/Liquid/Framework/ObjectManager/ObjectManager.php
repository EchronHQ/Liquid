<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager;

use DI\Container;

class ObjectManager implements ObjectManagerInterface
{


    public function __construct(
        private readonly Container $factory,
        private readonly Config    $config
    )
    {

    }

    /**
     * @inheritdoc
     */
    public function create(string $type, array $arguments = []): mixed
    {
        return $this->factory->make($this->config->getPreference($type), $arguments);
    }

    /**
     * @inheritdoc
     */
    public function get(string $type): mixed
    {
        $type = \ltrim($type, '\\');
        $type = $this->config->getPreference($type);
        return $this->factory->get($type);
    }

    /**
     * @inheritdoc
     */
    public function configure(array $configuration): void
    {
        $this->config->extend($configuration);
    }

    /**
     * @inheritdoc
     */
    public function has(string $type): bool
    {
        return $this->factory->has($type);
    }
}
