<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager;

use DI\Container;
use DI\ContainerBuilder;

class ObjectManager implements ObjectManagerInterface
{
    /**
     * List of shared instances
     *
     * @var array
     */
    private array $_sharedInstances = [];
    private Container $factory;

    public function __construct(
        private readonly Config $config,
        array                   &$sharedInstances
    )
    {
        $this->_sharedInstances = &$sharedInstances;
        $this->_sharedInstances[ObjectManagerInterface::class] = $this;
        $this->_sharedInstances[ObjectManager::class] = $this;
        $this->buildObjectManager();
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
        if (!isset($this->_sharedInstances[$type])) {
            $this->_sharedInstances[$type] = $this->factory->get($type);
        }
        return $this->_sharedInstances[$type];
    }

    /**
     * @inheritdoc
     */
    public function configure(array $configuration): void
    {
        $this->config->extend($configuration);
        // When configuration changes we need to re-build the object manager
        $this->buildObjectManager();
    }


    /**
     * @inheritdoc
     */
    public function has(string $type): bool
    {

        return $this->factory->has($type);
    }

    private function buildObjectManager(): void
    {

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAttributes(true);
        // $containerBuilder->enableDefinitionCache('lq');
        // $containerBuilder->enableCompilation(ROOT . 'var/cache');

        $containerBuilder->addDefinitions($this->_sharedInstances);
        $containerBuilder->addDefinitions($this->config->getDefinitions());

        $containerBuilder->wrapContainer($this);
        $this->factory = $containerBuilder->build();
    }
}
