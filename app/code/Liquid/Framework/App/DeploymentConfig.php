<?php
declare(strict_types=1);

namespace Liquid\Framework\App;

use Liquid\Content\Model\Locale;
use Liquid\Framework\App\Config\Reader;
use Liquid\Framework\Config\ConfigOptionsListConstants;
use Liquid\Framework\Exception\FileSystemException;
use Liquid\Framework\Exception\RuntimeException;

class DeploymentConfig
{
    private const string LIQUID_ENV_PREFIX = 'LQ_DC_';
    private const string ENV_NAME_PATTERN = '~^#env\(\s*(?<name>\w+)\s*(,\s*"(?<default>[^"]+)")?\)$~';
    private const string OVERRIDE_KEY = self::LIQUID_ENV_PREFIX . '_OVERRIDE';

    private array $data = [];
    private AppMode|null $mode = null;
    private array $readerLoad = [];
    private array|null $envOverrides = null;
    private array|null $flatData = null;

    public function __construct(
        private readonly Reader $configReader,
        private readonly array  $overrideData = []
    )
    {

    }

    public function setValue(string $key, mixed $value): void
    {
        if (isset($this->data[$key])) {
            throw new \Exception('Value already exists');
        }
        $this->data[$key] = $value;
    }

    public function getValueString(string $key, string|null $default = null): string
    {
        return $this->getValue($key, $default) . '';
    }

    public function getValue(string|null $key, string|int|null $defaultValue = null): mixed
    {
        if ($key === null) {
            if ($this->flatData === null) {
                $this->reloadData();
            }
            return $this->flatData;
        }
        $x = new \Exception('a');

//        echo $key . PHP_EOL;
//        echo $x->getTraceAsString() . PHP_EOL;
        $result = $this->getByKey($key);
        if ($result === null) {
            if ($this->flatData === null || $this->envOverrides === null) {
                $this->reloadData();
            }
            $result = $this->getByKey($key);
        }
        return $result ?? $defaultValue;
    }

    public function getValueBoolean(string $key, bool|null $default = null): bool
    {
        $x = $this->getValue($key, '');
        if ($x === '') {
            if ($default === null) {
                throw new \Exception('Config value "' . $key . '" not found');
            }
            return $default;
        }

        return (bool)$x;
    }

    public function getMode(): AppMode
    {
        if ($this->mode === null) {
            if ($this->getValue('app.mode', AppMode::Production->name) === 'develop') {
                $this->mode = AppMode::Develop;
            } else {
                $this->mode = AppMode::Production;
            }
        }
        return $this->mode;
    }

    public function setLocale(Locale $locale, bool $defined): void
    {
        $this->data['current_locale'] = $locale;
        $this->data['current_locale_defined'] = $defined;
    }

    public function hasLocales(): bool
    {
        /** TODO: determine if system has more than 1 locale enabled or if this is a single locale system */
        return false;
    }

    public function getLocale(): Locale
    {
        if (!isset($this->data['current_locale'])) {
            throw new \Exception('Locale not defined');
        }
        return $this->data['current_locale'];
    }

    public function isLocaleDefined(): bool
    {
        if ($this->isCLI()) {
            // TODO: implement locale emulation
            return false;
        }
        return isset($this->data['current_locale_defined']) && $this->data['current_locale_defined'] === true;
    }

    public function isCLI(): bool
    {
        return PHP_SAPI === 'cli';
    }


    final public function debugTerms(): bool
    {
        return false;
        // return $this->mode === ApplicationMode::DEVELOP;
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

    /**
     * Checks if data available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function isAvailable(): bool
    {
        return $this->getValue(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE) !== null;
    }

    /**
     * Check if data from deploy files is available
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function isDbAvailable(): bool
    {
        return true;
        return $this->getConfigData(ConfigOptionsListConstants::CONFIG_PATH_DB) !== null;
    }

    /**
     * Returns flat data by key
     *
     * @param string|null $key
     * @return mixed|null
     */
    private function getByKey(string|null $key): mixed
    {
        if ($this->flatData === null) {
            return null;
        }
        if (\array_key_exists($key, $this->flatData) && $this->flatData[$key] === null) {
            return '';
        }

        return $this->flatData[$key] ?? null;
    }

    /**
     * Loads the configuration data
     *
     * @return void
     * @throws \Liquid\Framework\Exception\FileSystemException
     * @throws \Liquid\Framework\Exception\RuntimeException
     */
    private function reloadData(): void
    {
        $this->reloadInitialData();
        // flatten data for config retrieval using get()
        $this->flatData = $this->flattenParams($this->data);
        $this->flatData = $this->getAllEnvOverrides() + $this->flatData;
    }

    private function flattenParams(array $params, string|null $path = null, array|null &$flattenResult = null): array
    {
        if (null === $flattenResult) {
            $flattenResult = [];
        }

        foreach ($params as $key => $param) {
            if ($path) {
                $newPath = $path . '/' . $key;
            } else {
                $newPath = $key;
            }
            if (isset($flattenResult[$newPath])) {
                throw new \Liquid\Framework\Exception\RuntimeException("Key collision '" . $newPath . "' is already defined.");
            }

            if (\is_array($param)) {
                $flattenResult[$newPath] = $param;
                $this->flattenParams($param, $newPath, $flattenResult);
            } else {
                // allow reading values from env variables
                // value need to be specified in %env(NAME, "default value")% format
                // like #env(DB_PASSWORD), #env(DB_NAME, "test")
                if ($param !== null && $param !== true && $param !== false && \preg_match(self::ENV_NAME_PATTERN, (string)$param, $matches)) {
                    $param = \getenv($matches['name']) ?: ($matches['default'] ?? null);
                }

                $flattenResult[$newPath] = $param;
            }
        }

        return $flattenResult;
    }

    /**
     * Load all getenv() configs once
     *
     * @return array
     */
    private function getAllEnvOverrides(): array
    {
        if ($this->envOverrides === null) {
            $this->envOverrides = [];
            // allow reading values from env variables by convention
            // LIQUID_DC_{path}, like db/connection/default/host =>
            // can be overwritten by LIQUID_DC_DB__CONNECTION__DEFAULT__HOST
            foreach (\getenv() as $key => $value) {
                if ($key !== self::OVERRIDE_KEY && str_contains($key, self::LIQUID_ENV_PREFIX)) {
                    // convert LIQUID_DC_DB__CONNECTION__DEFAULT__HOST into db/connection/default/host
                    $flatKey = \strtolower(\str_replace([self::LIQUID_ENV_PREFIX, '__'], ['', '/'], $key));
                    $this->envOverrides[$flatKey] = $value;
                }
            }
        }
        return $this->envOverrides;
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
        $this->data = \array_replace(
            $this->readerLoad,
            $this->overrideData ?? [],
            $this->getEnvOverride()
        );
    }

    /**
     * Get additional configuration from env variable LIQUID_DC__OVERRIDE
     *
     * Data should be JSON encoded
     *
     * @return array
     * @throws \JsonException
     */
    private function getEnvOverride(): array
    {
        $env = \getenv(self::OVERRIDE_KEY);
        return !empty($env) ? (\json_decode($env, true, 512, JSON_THROW_ON_ERROR) ?? []) : [];
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

//    private function automaticallyDetectSiteUrl(): string
//    {
//        // TODO: this is not good as
//        if ($this->isCLI()) {
//            $path = getcwd();
//            if ($path === '/var/www/html') {
//                return 'http://localhost:8900/';
//            }
//            if (StringHelper::contains($path, 'girasole')) {
//                return 'https://girasole.attlaz.com/';
//            }
//            return 'https://attlaz.com/';
//
//        }
//        $server = $_SERVER;
//        if (isset($server['HTTP_HOST'])) {
//            return (isset($server['HTTPS']) && $server['HTTPS'] === 'on' ? "https" : "http") . "://$server[HTTP_HOST]/";
//        }
//        return 'http://localhost:8900/';
//    }
//
//    private function detectCurrentUrl(): string
//    {
//        $server = $_SERVER;
//        return (isset($server['HTTPS']) && $server['HTTPS'] === 'on' ? "https" : "http") . "://$server[HTTP_HOST]$server[REQUEST_URI]";
//    }
}
