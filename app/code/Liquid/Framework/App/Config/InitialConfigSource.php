<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Config;

use Liquid\Framework\DataObject;

/**
 * Responsible for reading sources from files: config.dist.php, config.local.php, app/etc/config.yml
 */
class InitialConfigSource implements ConfigSourceInterface
{
    public function __construct(
        private readonly \Liquid\Framework\App\DeploymentConfig\Reader $reader,
        private readonly string                                        $configType,
    )
    {

    }

    /**
     * @inheritdoc
     */
    public function get(string $path = ''): array
    {
        $data = new DataObject($this->reader->load());
        if ($path !== '' && $path !== null) {
            $path = '/' . $path;
        }
        return $data->getData($this->configType . $path) ?: [];
    }
}
