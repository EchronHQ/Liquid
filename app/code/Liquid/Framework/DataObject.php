<?php
declare(strict_types=1);

namespace Liquid\Framework;
/**
 * Universal data container with array access implementation
 */
class DataObject
{
    private array $_data;

    public function __construct(array $data = [])
    {
        $this->_data = $data;
    }

    /**
     * Add data to the object.
     * Retains previous data in the object.
     *
     * @param array $arr
     * @return $this
     */
    public function addData(array $arr): self
    {
        if ($this->_data === []) {
            $this->setData($arr);
            return $this;
        }

        foreach ($arr as $index => $value) {
            $this->setData($index, $value);
        }
        return $this;
    }

    /**
     * Unset data from the object.
     *
     * @param array|string|null $key
     * @return $this
     */
    public function unsetData(array|null|string $key = null): self
    {
        if ($key === null) {
            $this->setData([]);
        } elseif (is_string($key)) {
            if (isset($this->_data[$key]) || array_key_exists($key, $this->_data)) {
                unset($this->_data[$key]);
            }
        } elseif ($key === (array)$key) {
            foreach ($key as $element) {
                $this->unsetData($element);
            }
        }
        return $this;
    }

    /**
     * If $key is empty, checks whether there's any data in the object.
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return bool
     */
    public function hasData(string $key = ''): bool
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->_data);
        }
        return array_key_exists($key, $this->_data);
    }

    /**
     * Convert array of object data with to array with keys requested in $keys array
     *
     * @param string[] $keys
     * @return array
     */
    public function toArray(array $keys = []): array
    {
        if (empty($keys)) {
            return $this->_data;
        }

        $result = [];
        foreach ($keys as $key) {
            if (isset($this->_data[$key])) {
                $result[$key] = $this->_data[$key];
            } else {
                $result[$key] = null;
            }
        }
        return $result;
    }

    /**
     * Checks whether the object is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        if (empty($this->_data)) {
            return true;
        }
        return false;
    }

    /**
     * Get defined keys in data
     *
     * @return string[]
     */
    public function getDataKeys(): array
    {
        return array_keys($this->_data);
    }

    /**
     * Get value from _data array without parse key
     *
     * @param string $key
     * @return mixed
     */
    protected function _getData(string $key): mixed
    {
        return $this->_data[$key] ?? null;
    }

    /**
     * Object data getter
     *
     *  If $key is not defined will return all the data as an array.
     *  Otherwise it will return value of the element specified by $key.
     *  It is possible to use keys like a/b/c for access nested array data
     *
     *  If $index is specified it will assume that attribute data is an array
     *  and retrieve corresponding member. If data is the string - it will be explode
     *  by new line character and converted to array.
     *
     * @param string $key
     * @param int|string|null $index
     * @return mixed
     */
    public function getData(string $key = '', int|string $index = null): mixed
    {
        if ('' === $key) {
            return $this->_data;
        }

        $data = $this->_data[$key] ?? null;
        if ($data === null && $key !== null && strpos($key, '/') !== false) {
            /* process a/b/c key as ['a']['b']['c'] */
            $data = $this->getDataByPath($key);
        }

        if ($index !== null) {
            if ($data === (array)$data) {
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif (is_string($data)) {
                $data = explode(PHP_EOL, $data);
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif ($data instanceof self) {
                $data = $data->getData($index);
            } else {
                $data = null;
            }
        }
        return $data;
    }

    /**
     * Overwrite data in the object.
     *
     *  The $key parameter can be string or array.
     *  If $key is string, the attribute value will be overwritten by $value
     *
     *  If $key is an array, it will overwrite all the data in the object.
     *
     * @param array|string $key
     * @param mixed|null $value
     * @return $this
     */
    public function setData(array|string $key, mixed $value = null): self
    {
        if ($key === (array)$key) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * Get object data by path
     *
     *  Method consider the path as chain of keys: a/b/c => ['a']['b']['c']
     *
     * @param string $path
     * @return mixed
     */
    public function getDataByPath(string $path): mixed
    {
        $keys = explode('/', (string)$path);

        $data = $this->_data;
        foreach ($keys as $key) {
            if ((array)$data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof self) {
                $data = $data->getDataByKey($key);
            } else {
                return null;
            }
        }
        return $data;
    }

    /**
     * Get object data by particular key
     *
     * @param string $key
     * @return mixed
     */
    public function getDataByKey(string $key): mixed
    {
        return $this->_getData($key);
    }
}
