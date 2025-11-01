<?php
declare(strict_types=1);

namespace Liquid\Admin\App\Area;

use Liquid\Admin\App\Config;
use Liquid\Framework\App\Area\FrontNameResolverInterface;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\Url\UriParser;

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
    private array $standardPorts = ['http' => '80', 'https' => '443'];
    private readonly string $defaultFrontName;

    public function __construct(
        private readonly Config      $config,
        private readonly ScopeConfig $segmentConfig,
        private readonly Request     $request,
        private readonly UriParser   $uriParser,
        DeploymentConfig             $deploymentConfig,
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
        // TODO: when we use {{base_url}} as unsecure/base_url then this doesn't work
        if ($this->segmentConfig->getValue(self::XML_PATH_USE_CUSTOM_ADMIN_URL)) {
            $backendUrl = $this->segmentConfig->getValue(self::XML_PATH_CUSTOM_ADMIN_URL);
        } else {

            $xmlPath = $this->request->isSecure() ? 'web/secure/base_url' : 'web/unsecure/base_url';

            $backendUrl = $this->config->getValue($xmlPath);
            if ($backendUrl === null) {
                $backendUrl = $this->segmentConfig->getValue($xmlPath);
            }
        }
        if ($backendUrl === '{{base_url}}') {
            // TODO: temporary workaround
            $backendUrl = 'http://localhost:8901/';
        }

        $uri = $this->uriParser->parse($backendUrl);
        $configuredHost = $uri->getHost();
        if (!$configuredHost) {
            return false;
        }
        $configuredPort = $uri->getPort() ?: ($this->standardPorts[$uri->getScheme()] ?? null);
        $uriString = ($this->request->isSecure() ? 'https' : 'http') . '://' . $this->request->getServer('HTTP_HOST');

        $uri = $this->uriParser->parse($uriString);
        $host = $uri->getHost();
        if ($configuredPort) {
            $configuredHost .= ':' . $configuredPort;
            $host .= ':' . ($uri->getPort() ?: $this->standardPorts[$uri->getScheme()]);
        }

        return \strcasecmp($configuredHost, $host) === 0;
    }
}
