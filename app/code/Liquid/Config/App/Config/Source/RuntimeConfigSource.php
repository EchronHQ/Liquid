<?php
declare(strict_types=1);

namespace Liquid\Config\App\Config\Source;

use Liquid\Content\Model\Segment\Segment;
use Liquid\Framework\App\Config\ConfigSourceInterface;
use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\DataObject;

/**
 * Class for retrieving runtime configuration from database.
 */
class RuntimeConfigSource implements ConfigSourceInterface
{
    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
    )
    {

    }

    public function get(string $path = ''): string|array|null
    {
        $data = new DataObject($this->deploymentConfig->isDbAvailable() ? $this->loadConfig() : []);

        return $data->getData($path);
    }

    /**
     * Load config from database.
     *
     * Load collection from db and presents it in array with path keys, like:
     * * scope/key/key *
     *
     * @return array
     */
    private function loadConfig(): array
    {
        return [
            'default' => [
                'web' => [
                    'unsecure' => [
//                        'base_url' => 'http://localhost:8901/',
                        'base_url' => Segment::BASE_URL_PLACEHOLDER,
                        'base_link_url' => Segment::BASE_URL_PLACEHOLDER,
                    ],
                    'secure' => [
                        'use_in_frontend' => true,
                        'base_url' => Segment::BASE_URL_PLACEHOLDER,
                        'base_link_url' => Segment::BASE_URL_PLACEHOLDER,
                    ],
                ],
                'admin' => [
                    'url' => [
                        'use_custom_path' => true,
                        'custom_path' => 'QuoSkO',
                        // This allows for a separate url that is different than the frontend url
                        'use_custom' => false,
                        'custom' => null,
                    ],
                ],
                'dev' => [
                    'minifyhtml' => true,
                    'minifycss' => true,
                    'translate_debug' => [
                        'active' => false,
                    ],
                ],
                // LOgging is defined in deployment config (etc/config.yml)
//                'logging' => [
//                    'browser' => [
//                        'minloglevel' => 'info',
//                    ],
//                ],
            ],
        ];
    }
}
