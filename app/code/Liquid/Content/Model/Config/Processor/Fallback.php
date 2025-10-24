<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Config\Processor;

use Liquid\Content\App\Config\Type\Scopes;
use Liquid\Content\Model\ResourceModel\Segment;
use Liquid\Content\Model\ResourceModel\Website;
use Liquid\Content\Model\ScopeType;
use Liquid\Framework\App\DeploymentConfig;

class Fallback
{
    private array $segmentData = [];
    private array $websiteData = [];
    private array $websiteNonStdCodes = [];
    private array $storeNonStdCodes = [];

    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
        private readonly Segment          $segmentResource,
        private readonly Website          $websiteResource,
        private readonly Scopes           $scopes
    )
    {

    }

    /**
     * Process config after reading and converting to appropriate format
     */
    public function process(array $data): array
    {
        $this->loadScopes();

        $defaultConfig = $data[ScopeType::DEFAULT->value] ?? [];
        $websitesConfig = $data[ScopeType::WEBSITE->value] ?? [];
        $segmentsConfig = $data[ScopeType::SEGMENT->value] ?? [];

        return [
            ScopeType::DEFAULT->value => $defaultConfig,
            ScopeType::WEBSITE->value => $this->prepareWebsitesConfig($defaultConfig, $websitesConfig),
            ScopeType::SEGMENT->value => $this->prepareSegmentsConfig($defaultConfig, $websitesConfig, $segmentsConfig),
        ];
    }

    /**
     * Prepare stores data from Config/Type/Scopes
     *
     * @param array $defaultConfig
     * @param array $websitesConfig
     * @param array $segmentsConfig
     * @return array
     */
    private function prepareSegmentsConfig(
        array $defaultConfig,
        array $websitesConfig,
        array $segmentsConfig
    ): array
    {
        $result = [];

        foreach ($this->segmentData as $segment) {
            $code = $segment['code'];
            $id = $segment['id'];
            $websiteConfig = [];
            if (isset($segment['website_id'])) {
                $websiteConfig = $this->getWebsiteConfig($websitesConfig, $segment['website_id']);
            }
            $storeConfig = $this->mapEnvStoreToStore($segmentsConfig, $code);
            $result[$code] = array_replace_recursive($defaultConfig, $websiteConfig, $storeConfig);
            $result[strtolower($code)] = $result[$code];
            $result[$id] = $result[$code];
        }
        return $result;
    }

    /**
     * Find information about website by its ID.
     *
     * @param array $websites Has next format: (website_code => [website_data])
     * @param string $id
     * @return array
     */
    private function getWebsiteConfig(array $websites, string $id): array
    {
        foreach ($this->websiteData as $website) {
            if ($website['id'] === $id) {
                $code = $website['code'];
                $nonStdConfigs = $this->getTheEnvConfigs($websites, $this->websiteNonStdCodes, $code);
                $stdConfigs = $websites[$code] ?? [];
                return \count($nonStdConfigs) ? $stdConfigs + $nonStdConfigs : $stdConfigs;
            }
        }
        return [];
    }

    /**
     * Map $_ENV lower cased store codes to upper-cased and camel cased store codes to get the proper configuration
     *
     * @param array $configs
     * @param string $code
     * @return array
     */
    private function mapEnvStoreToStore(array $configs, string $code): array
    {
        if (!\count($this->storeNonStdCodes)) {
            $this->storeNonStdCodes = \array_diff(\array_keys($configs), \array_column($this->segmentData, 'code'));
        }

        return $this->getTheEnvConfigs($configs, $this->storeNonStdCodes, $code);
    }

    /**
     * Get all $_ENV configs from non-matching store/website codes
     *
     * @param array $configs
     * @param array $nonStdCodes
     * @param string $code
     * @return array
     */
    private function getTheEnvConfigs(array $configs, array $nonStdCodes, string $code): array
    {
        $additionalConfigs = [];
        foreach ($nonStdCodes as $nonStdStoreCode) {
            if (\strtolower($nonStdStoreCode) === \strtolower($code)) {
                $additionalConfigs = $this->getConfigsByNonStandardCodes($configs, $nonStdStoreCode, $code);
            }
        }

        return \count($additionalConfigs) ? $additionalConfigs : ($configs[$code] ?? []);
    }

    /**
     * Match non-standard website/store codes with internal codes
     *
     * @param array $configs
     * @param string $nonStdCode
     * @param string $internalCode
     * @return array
     */
    private function getConfigsByNonStandardCodes(array $configs, string $nonStdCode, string $internalCode): array
    {
        $internalCodeConfigs = $configs[$internalCode] ?? [];
        if (\strtolower($internalCode) === \strtolower($nonStdCode)) {
            return isset($configs[$nonStdCode]) ?
                $internalCodeConfigs + $configs[$nonStdCode]
                : $internalCodeConfigs;
        }
        return $internalCodeConfigs;
    }

    /**
     * Prepare website data from Config/Type/Scopes
     *
     * @param array $defaultConfig
     * @param array $websitesConfig
     * @return array
     */
    private function prepareWebsitesConfig(
        array $defaultConfig,
        array $websitesConfig
    ): array
    {
        $result = [];
        foreach ($this->websiteData as $website) {
            $code = $website['code'];
            $id = $website['id'];
            $websiteConfig = $this->mapEnvWebsiteToWebsite($websitesConfig, $code);
            $result[$code] = \array_replace_recursive($defaultConfig, $websiteConfig);
            $result[$id] = $result[$code];
        }
        return $result;
    }

    /**
     * Map $_ENV lower cased website codes to upper-cased and camel cased website codes to get the proper configuration
     *
     * @param array $configs
     * @param string $code
     * @return array
     */
    private function mapEnvWebsiteToWebsite(array $configs, string $code): array
    {
        if (!\count($this->websiteNonStdCodes)) {
            $this->websiteNonStdCodes = \array_diff(\array_keys($configs), \array_keys($this->websiteData));
        }

        return $this->getTheEnvConfigs($configs, $this->websiteNonStdCodes, $code);
    }

    /**
     * Load config from database.
     */
    private function loadScopes(): void
    {
        try {
            if ($this->deploymentConfig->isDbAvailable()) {
                $this->segmentData = $this->segmentResource->readAllSegments();
                $this->websiteData = $this->websiteResource->readAllWebsites();
            } else {
                $this->segmentData = $this->scopes->get('stores');
                $this->websiteData = $this->scopes->get('websites');
            }

        } catch (\Throwable $exception) {
            //TableNotFoundException $exception
            // database is empty or not setup
            $this->segmentData = [];
            $this->websiteData = [];
        }
    }
}
