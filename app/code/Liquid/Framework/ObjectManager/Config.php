<?php
declare(strict_types=1);

namespace Liquid\Framework\ObjectManager;

use Liquid\Framework\Exception\ContextException;
use function DI\autowire;
use function DI\get;

class Config
{
    public static string $TYPE_OBJECT = 'object';
    public static string $TYPE_ARRAY = 'array';
    public static string $TYPE_STRING = 'string';
    public static string $TYPE_CONST = 'const';
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
        $type = $type !== null ? \ltrim($type, '\\') : '';
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
//                $this->_currentCacheKey = md5(
//                    $this->getSerializer()->serialize(
//                        [$this->_arguments, $this->_nonShared, $this->_preferences, $this->_virtualTypes]
//                    )
//                );
//            }
//            // md5() here is not for cryptographic use.
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

    public function getDefinitions(): array
    {
        $output = [];

        /**
         * Format preferences
         */
        $preferences = $this->preferences;
        foreach ($preferences as $for => $to) {
            $output[$for] = get($to);
        }
        /**
         * Format types
         */
        $types = \array_unique(\array_keys($this->arguments));
        foreach ($types as $type) {
            $arguments = $this->getArguments($type);

            $parameters = [];
            foreach ($arguments as $argumentName => $argument) {
                // TODO: we use the array key as argument name now, but we also still have a property of arguments['name']. This is confusing.
                if (\is_numeric($argumentName) && isset($argument['name'])) {
                    $argumentName = $argument['name'];
                }
                if (\is_string($argument)) {
                    throw new ContextException('Argument `' . $argumentName . '` should be array, string given', [
                        'arguments' => $arguments,
                        'value' => $argument,
                    ]);
                }
                $parameters[$argumentName] = $this->formatToDi($argument);

            }

            //$output[$type] = \DI\autowire()->constructorParameter();
            $output[$type] = autowire()->constructor(... $parameters);
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
                    $arguments = \array_replace_recursive($arguments, $this->arguments[$type]);
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
                        $this->preferences[\ltrim($for, '\\')] = \ltrim($to, '\\');
                    }
                    break;
                case 'types':
                    foreach ($curConfig as $for => $to) {

                        $name = $for;


//                            $this->virtualTypes[$name] = ltrim($curConfig['type'], '\\');
//                        }

                        $arguments = $to['arguments'];
                        //if ($for === 'arguments') {
                        //  var_dump($for);
                        if (!empty($this->mergedArguments)) {


                            $this->mergedArguments = [];
                        }

                        if (isset($this->arguments[$name])) {


                            // Merge if type array
                            foreach ($arguments as $argument => $argumentData) {
                                if ($argumentData['type'] === 'array') {
                                    $existingValues = $this->arguments[$name][$argument]['value'];
                                    $newValues = $argumentData['value'];

                                    $this->arguments[$name][$argument]['value'] = \array_replace($existingValues, $newValues);
                                } else {
                                    $this->arguments[$name][$argument]['value'] = $argumentData['value'];
                                }
                            }

                            // $this->arguments[$name] = array_replace( $this->arguments[$name],$arguments);


                        } else {
                            $this->arguments[$name] = $arguments;
                        }
//                        if ($name === RouterList::class) {
//                            var_dump($arguments);
//                            var_dump($this->arguments[$name]);
//                        }
                        //  }

                        //  $this->mergeConfiguration($curConfig);
                    }
                    break;

                default:
                    var_dump($curConfig);
                    die('--- wrong config? (unknown configuration key) ---');
                    $key = ltrim($key, '\\');
                    var_dump($key);
                    var_dump($curConfig);
                    if (isset($curConfig['type'])) {
                        $this->virtualTypes[$key] = \ltrim($curConfig['type'], '\\');
                    }
                    if (isset($curConfig['arguments'])) {
                        if (!empty($this->mergedArguments)) {
                            $this->mergedArguments = [];
                        }
                        if (isset($this->arguments[$key])) {
                            $this->arguments[$key] = \array_replace($this->arguments[$key], $curConfig['arguments']);
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

    private function formatToDi(array $argument): mixed
    {
        $argumentType = $argument['type'] ?? null;
        if (!isset($argument['value'])) {
            throw new \RuntimeException('Config argument is missing value property');
            // throw new \RuntimeException('Config argument "' . $argumentName . '" for type "' . $type . '" is missing value');
        }
        $argumentValue = $argument['value'];
        // TODO: make this into a constant?
        if ($argumentType === 'object') {
            return get($argumentValue);
        }

        if ($argumentType === self::$TYPE_ARRAY) {
            $values = [];
            foreach ($argumentValue as $subArgumentKey => $subArgumentValue) {
                if (isset($subArgumentValue['type'])) {
                    $subArgumentValue = $this->formatToDi($subArgumentValue);
                }
                $values[$subArgumentKey] = $subArgumentValue;

            }
            return $values;
        }

        if ($argumentType === self::$TYPE_STRING || $argumentType === null) {
            return $argumentValue;
        }
        if ($argumentType === self::$TYPE_CONST) {
            return $argumentValue;
        }

        throw new \RuntimeException('Unknown config argument type "' . $argumentType . '"');
    }
}
