<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;


use Liquid\Framework\Exception\FileSystemException;
use Liquid\Framework\Exception\RuntimeException;

/**
 * @deprecated
 */
class AppConfig
{
    /**
     * Flattened data
     *
     * @var array
     */
    private array $flatData = [];
    private array|null $readerLoad = null;
    private array|null $data = null;

    public function __construct(
        private readonly Reader $configReader,
        private readonly array  $overrideData = []
    )
    {

    }

    public function getBool(string $key, bool|null $defaultValue = null): bool
    {
        $value = $this->get($key);
        if ($value === true) {
            return true;
        }
        if ($value === false) {
            return false;
        }
        if ($defaultValue !== null) {
            return $defaultValue;
        }
        throw new \Exception('Config `' . $key . '` not defined');
    }

    /**
     * Gets data from flattened data
     *
     * @param string|null $key
     * @param mixed $defaultValue
     * @return mixed|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function get(string|null $key = null, mixed $defaultValue = null): mixed
    {
        if ($key === null) {
            if (empty($this->flatData)) {
                $this->reloadData();
            }
            return $this->flatData;
        }
        $result = $this->getByKey($key);
        if ($result === null) {
            if (empty($this->flatData)) {
                $this->reloadData();
            }
            $result = $this->getByKey($key);
        }
        return $result ?? $defaultValue;
    }

    /**
     * Gets a value specified key from config data
     *
     * @param string|null $key
     * @return null|mixed
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getConfigData(string|null $key = null): mixed
    {
        if ($key === null) {
            if (empty($this->data)) {
                $this->reloadInitialData();
            }
            return $this->data;
        }
        $result = $this->getConfigDataByKey($key);
        if ($result === null) {
            $this->reloadInitialData();
            $result = $this->getConfigDataByKey($key);
        }
        return $result;
    }

    private function reloadData(): void
    {
        $this->reloadInitialData();
    }

    /**
     * Loads the configuration data
     *
     * @return void
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function reloadInitialData(): void
    {
        if (empty($this->readerLoad) || empty($this->data) || empty($this->flatData)) {
            $this->readerLoad = $this->configReader->load();
        }
        $this->data = array_replace(
            $this->readerLoad,
            $this->overrideData ?? []
        );
    }

    /**
     * Returns flat data by key
     *
     * @param string|null $key
     * @return mixed|null
     */
    private function getByKey(string|null $key): mixed
    {
        if (array_key_exists($key, $this->flatData) && $this->flatData[$key] === null) {
            return '';
        }

        return $this->flatData[$key] ?? null;
    }

    /**
     * Returns data by key
     *
     * @param string|null $key
     * @return mixed|null
     */
    private function getConfigDataByKey(string|null $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}
