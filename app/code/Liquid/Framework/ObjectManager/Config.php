<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager;

class Config
{
    /**
     * List of merged arguments
     *
     * @var array
     */
    private array $mergedArguments = [];

    private array $preferences = [];
    private array $virtualTypes = [];
    /**
     * Instance arguments
     *
     * @var array
     */
    private array $arguments = [];

    /**
     * Retrieve preference for type
     *
     * @param string $type
     * @return string
     * @throws \LogicException
     */
    public function getPreference(string $type): string
    {
        $type = $type !== null ? ltrim($type, '\\') : '';
        $preferencePath = [];

        while (isset($this->preferences[$type])) {
            if (isset($preferencePath[$this->preferences[$type]])) {
                throw new \LogicException(
                    'Circular type preference: ' .
                    $type .
                    ' relates to ' .
                    $this->preferences[$type] .
                    ' and viceversa.'
                );
            }
            $type = $this->preferences[$type];
            $preferencePath[$type] = 1;
        }
        return $type;
    }

    /**
     * Extend configuration
     *
     * @param array $configuration
     * @return void
     */
    public function extend(array $configuration)
    {
//        if ($this->_cache) {
//            if (!$this->_currentCacheKey) {
//                // md5() here is not for cryptographic use.
//                // phpcs:ignore Magento2.Security.InsecureFunction
//                $this->_currentCacheKey = md5(
//                    $this->getSerializer()->serialize(
//                        [$this->_arguments, $this->_nonShared, $this->_preferences, $this->_virtualTypes]
//                    )
//                );
//            }
//            // md5() here is not for cryptographic use.
//            // phpcs:ignore Magento2.Security.InsecureFunction
//            $key = md5($this->_currentCacheKey . $this->getSerializer()->serialize($configuration));
//            $cached = $this->_cache->get($key);
//            if ($cached) {
//                [
//                    $this->_arguments,
//                    $this->_nonShared,
//                    $this->_preferences,
//                    $this->_virtualTypes,
//                    $this->_mergedArguments,
//                ] = $cached;
//            } else {
//                $this->_mergeConfiguration($configuration);
//                if (!$this->_mergedArguments) {
//                    foreach ($this->_definitions->getClasses() as $class) {
//                        $this->_collectConfiguration($class);
//                    }
//                }
//                $this->_cache->save(
//                    [
//                        $this->_arguments,
//                        $this->_nonShared,
//                        $this->_preferences,
//                        $this->_virtualTypes,
//                        $this->_mergedArguments,
//                    ],
//                    $key
//                );
//            }
//            $this->_currentCacheKey = $key;
//        } else {
        $this->mergeConfiguration($configuration);
//        }
    }

    /**
     * Merge configuration
     *
     * @param array $configuration
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function mergeConfiguration(array $configuration): void
    {
        foreach ($configuration as $key => $curConfig) {
            switch ($key) {
                case 'preferences':
                    foreach ($curConfig as $for => $to) {
                        $this->preferences[ltrim($for, '\\')] = ltrim($to, '\\');
                    }
                    break;
                case 'types':
                    foreach ($curConfig as $for => $to) {

                        $name = $for;
//                        if (isset($to['type'])) {
//                            $this->virtualTypes[$name] = ltrim($curConfig['type'], '\\');
//                        }

                        $arguments = $to['arguments'];
                        //if ($for === 'arguments') {
                        //  var_dump($for);
                        if (!empty($this->mergedArguments)) {
                            $this->mergedArguments = [];
                        }
                        if (isset($this->arguments[$name])) {
                            $this->arguments[$name] = array_replace($this->arguments[$name], $arguments);
                        } else {
                            $this->arguments[$name] = $arguments;
                        }
                        //  }

                        //  $this->mergeConfiguration($curConfig);
                    }
                    break;

                default:
                    var_dump($curConfig);
                    die('--- wrong config? ---');
                    $key = ltrim($key, '\\');
                    var_dump($key);
                    var_dump($curConfig);
                    if (isset($curConfig['type'])) {
                        $this->virtualTypes[$key] = ltrim($curConfig['type'], '\\');
                    }
                    if (isset($curConfig['arguments'])) {
                        if (!empty($this->mergedArguments)) {
                            $this->mergedArguments = [];
                        }
                        if (isset($this->arguments[$key])) {
                            $this->arguments[$key] = array_replace($this->arguments[$key], $curConfig['arguments']);
                        } else {
                            $this->arguments[$key] = $curConfig['arguments'];
                        }

                        var_dump($this->arguments);
                    }
//                    if (isset($curConfig['shared'])) {
//                        if (!$curConfig['shared']) {
//                            $this->nonShared[$key] = 1;
//                        } else {
//                            unset($this->nonShared[$key]);
//                        }
//                    }
                    break;
            }
        }
    }

    public function getDefinitions(): array
    {
        $output = [];

        /**
         * Format preferences
         */
        $preferences = $this->preferences;
        foreach ($preferences as $for => $to) {
            $output[$for] = \DI\get($to);
        }
        /**
         * Format types
         */
        $types = array_unique(array_keys($this->arguments));
        foreach ($types as $type) {
            $arguments = $this->getArguments($type);

            $parameters = [];
            foreach ($arguments as $argumentName => $argument) {
                // TODO: we use the array key as argument name now, but we also still have a property of arguments['name']. This is confusing.
                if (is_numeric($argumentName) && isset($argument['name'])) {
                    $argumentName = $argument['name'];
                }
                $parameters[$argumentName] = $this->formatToDi($argument);

            }

            //$output[$type] = \DI\autowire()->constructorParameter();
            $output[$type] = \DI\autowire()->constructor(... $parameters);
        }
        return $output;
    }

    /**
     * Retrieve list of arguments per type
     *
     * @param string $type
     * @return array
     */
    public function getArguments(string $type): array
    {
        if (isset($this->mergedArguments[$type])) {
            return $this->mergedArguments[$type];
        }
        return $this->collectConfiguration($type);
    }

    /**
     * Collect parent types configuration for requested type
     *
     * @param string $type
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function collectConfiguration(string $type): array
    {
        if (!isset($this->mergedArguments[$type])) {
//            if (isset($this->virtualTypes[$type])) {
//                $arguments = $this->collectConfiguration($this->virtualTypes[$type]);
            // $arguments = $this->sortItemsHelper->sortItems($arguments);
//            } elseif ($this->_relations->has($type)) {
//                $relations = $this->_relations->getParents($type);
//                $arguments = [];
//                foreach ($relations as $relation) {
//                    if ($relation) {
//                        $relationArguments = $this->_collectConfiguration($relation);
//                        if ($relationArguments) {
//                            $arguments = array_replace($arguments, $relationArguments);
//                            // $arguments = $this->sortItemsHelper->sortItems($arguments);
//                        }
//                    }
//                }
//            } else {
            $arguments = [];
//            }

            if (isset($this->arguments[$type])) {
                if ($arguments && count($arguments)) {
                    $arguments = array_replace_recursive($arguments, $this->arguments[$type]);
                    // $arguments = $this->sortItemsHelper->sortItems($arguments);
                } else {
                    $arguments = $this->arguments[$type];
                }
            }
            $this->mergedArguments[$type] = $arguments;
            return $arguments;
        }
        return $this->mergedArguments[$type];
    }

    private function formatToDi(array $argument): mixed
    {
        $argumentType = $argument['type'] ?? null;
        if (!isset($argument['value'])) {
            throw new \RuntimeException('Config argument is missing value property');
            // throw new \RuntimeException('Config argument "' . $argumentName . '" for type "' . $type . '" is missing value');
        }
        $argumentValue = $argument['value'];
        if ($argumentType === 'object') {
            return \DI\get($argumentValue);
        }

        if ($argumentType === 'array') {
            $values = [];
            foreach ($argumentValue as $subArgumentKey => $subArgumentValue) {
                if (isset($subArgumentValue['type'])) {
                    $subArgumentValue = $this->formatToDi($subArgumentValue);
                }
                $values[$subArgumentKey] = $subArgumentValue;

            }
            return $values;
        }

        if ($argumentType === 'string' || $argumentType === null) {
            return $argumentValue;
        }

        throw new \RuntimeException('Unknown config argument type "' . $argumentType . '"');
    }
}
