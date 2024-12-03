<?php
declare(strict_types=1);

namespace Liquid\Admin\App\Area;

use Laminas\Uri\Uri;
use Liquid\Admin\App\Config;
use Liquid\Framework\App\Area\FrontNameResolverInterface;
use Liquid\Framework\App\Config\SegmentConfig;
use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\App\Request\Request;

class FrontNameResolver implements FrontNameResolverInterface
{
    public const string XML_PATH_USE_CUSTOM_ADMIN_PATH = 'admin/url/use_custom_path';

    public const string XML_PATH_CUSTOM_ADMIN_PATH = 'admin/url/custom_path';

    public const string XML_PATH_USE_CUSTOM_ADMIN_URL = 'admin/url/use_custom';

    public const string XML_PATH_CUSTOM_ADMIN_URL = 'admin/url/custom';

    /**
     * Backend area code
     */
    public const string AREA_CODE = 'adminhtml';

    private readonly string $defaultFrontName;

    public function __construct(
        private readonly Config        $config,
        private readonly SegmentConfig $segmentConfig,
        private readonly Request       $request,
        private readonly Uri           $uri,
        DeploymentConfig               $deploymentConfig,
    )
    {
        $this->defaultFrontName = $deploymentConfig->getValueString('backend/frontName');
    }

    public function getFrontName(bool $checkHost = false): string|null
    {
        if ($checkHost && !$this->isHostBackend()) {
            return null;
        }
        $isCustomPathUsed = (bool)(string)$this->config->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_PATH);
        if ($isCustomPathUsed) {
            return (string)$this->config->getValue(self::XML_PATH_CUSTOM_ADMIN_PATH);
        }
        return $this->defaultFrontName;
    }

    public function isHostBackend(): bool
    {
        if ($this->segmentConfig->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_URL)) {
            $backendUrl = $this->segmentConfig->getValue(self::XML_PATH_CUSTOM_ADMIN_URL);
        } else {
            $backendUrl = $this->config->getValue('web/unsecure/base_url');
            if ($backendUrl === null) {
                $backendUrl = $this->segmentConfig->getValue('web/unsecure/base_url');
            }
        }

        $host = (string)$this->request->getServer('HTTP_HOST', '');
        $hostWithPort = $this->getHostWithPort($backendUrl);

        return !($hostWithPort === null || $host === '') && stripos($hostWithPort, $host) !== false;
    }

    /**
     * Get host with port
     *
     * @param string $url
     * @return string|null
     */
    private function getHostWithPort(string $url): string|null
    {
        $this->uri->parse($url);
        $scheme = $this->uri->getScheme();
        $host = $this->uri->getHost();
        $port = $this->uri->getPort();
        if (!$port) {
            $port = $this->standardPorts[$scheme] ?? null;
        }
        return $port !== null ? $host . ':' . $port : $host;
    }
}
