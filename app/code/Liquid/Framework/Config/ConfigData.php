<?php
declare(strict_types=1);

namespace Liquid\Framework\Config;

class ConfigData
{
    protected array $data = [];

    /**
     * Get config value by key
     *
     * @param string|null $path
     * @param mixed $default
     * @return array|mixed|null
     */
    public function get(string|null $path = null, mixed $default = null): mixed
    {
        if ($path === null) {
            return $this->data;
        }
        $keys = explode('/', $path);
        $data = $this->data;
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        return $data;
    }

    /**
     * Merge config data to the object
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config): void
    {
        $this->data = array_replace_recursive($this->data, $config);
    }
}
