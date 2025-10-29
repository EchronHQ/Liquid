<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;

use Liquid\Framework\Exception\RuntimeException;
use Liquid\Framework\Filesystem\DirectoryList;
use Liquid\Framework\Filesystem\Path;
use Symfony\Component\Yaml\Yaml;

class Reader
{
    public const string APP_CONFIG = 'app_config';
    public const string APP_ENV = 'app_env';

    // TODO: add additional config files if needed?
    private array $applicationConfigFiles = [
        // General application configuration (which modules are active)
        self::APP_CONFIG => 'config.yml',
        // Contains environment specific information (usually contains secrets)
        self::APP_ENV => 'env.yml',
    ];

    public function __construct(
        private readonly DirectoryList $directoryList
    )
    {

    }

    public function load(): array
    {
        $path = $this->directoryList->getPath(Path::CONFIG);
        $result = [];
        foreach ($this->applicationConfigFiles as $configFile) {
            $configFilePath = $path . '/' . $configFile;
            if (\file_exists($configFilePath)) {
                $fileData = Yaml::parseFile($configFilePath);
                if (!\is_array($fileData)) {
                    throw new RuntimeException("Invalid configuration file: '" . $configFilePath . "'");
                }
                if ($fileData) {
                    $result = array_replace_recursive($result, $fileData);
                }
            }

        }
        return $result;
    }
}
