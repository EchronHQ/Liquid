<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Segment;

use Liquid\Content\Model\ScopeType;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\Scope\ScopeInterface as AppScopeInterface;
use Liquid\Framework\DataObject;
use Liquid\Framework\Escaper;
use Liquid\Framework\Filesystem\Filesystem;
use Liquid\Framework\Filesystem\Path;
use Liquid\Framework\Url;
use Liquid\Framework\Url\ScopeInterface as UrlScopeInterface;
use Liquid\Framework\Url\UrlType;

class Segment extends DataObject implements AppScopeInterface, UrlScopeInterface
{
    /**
     * A placeholder for generating base URL
     */
    public const string BASE_URL_PLACEHOLDER = '{{base_url}}';

    public const string XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';

    public const string  XML_PATH_SECURE_BASE_LINK_URL = 'web/secure/base_link_url';
    public const string  XML_PATH_UNSECURE_BASE_LINK_URL = 'web/unsecure/base_link_url';

    public const string  XML_PATH_SECURE_BASE_STATIC_URL = 'web/secure/base_static_url';
    public const string  XML_PATH_UNSECURE_BASE_STATIC_URL = 'web/unsecure/base_static_url';

    public const string  XML_PATH_UNSECURE_BASE_URL = 'web/unsecure/base_url';
    public const string  XML_PATH_SECURE_BASE_URL = 'web/secure/base_url';

    public const string XML_PATH_SECURE_BASE_MEDIA_URL = 'web/secure/base_media_url';
    public const string XML_PATH_UNSECURE_BASE_MEDIA_URL = 'web/unsecure/base_media_url';

    public SegmentId $id;
    public string $code;
    private array $baseUrlCache = [];
    private bool|null $_isFrontSecure = null;

    public function __construct(
        private readonly ScopeConfig $config,
        private readonly Request     $request,
        private readonly Filesystem  $filesystem,
        private readonly Url         $url,
        private readonly Escaper     $escaper,
        array                        $data = []
    )
    {
        parent::__construct($data);
    }

    public function getId(): SegmentId
    {
        return $this->id;
    }

    public function getBaseUrl(UrlType $type = UrlType::LINK, bool|null $secure = null): string
    {
        $cacheKey = $type->name . '/' . ($secure === null ? 'null' : ($secure ? 'true' : 'false'));
        if (!isset($this->baseUrlCache[$cacheKey])) {

            switch ($type) {
                case UrlType::WEB:
                    $path = $secure
                        ? self::XML_PATH_SECURE_BASE_URL
                        : self::XML_PATH_UNSECURE_BASE_URL;
                    $url = $this->getConfig($path);
                    break;
                case UrlType::LINK:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_LINK_URL : self::XML_PATH_UNSECURE_BASE_LINK_URL;
                    $url = $this->getConfig($path);
//                    $url = $this->_updatePathUseRewrites($url);
                    $url = $this->updatePathUseSegmentCode($url);
                    break;
                case UrlType::STATIC:
                    $path = $secure ? self::XML_PATH_SECURE_BASE_STATIC_URL : self::XML_PATH_UNSECURE_BASE_STATIC_URL;
                    $url = $this->getConfig($path);
                    if (!$url) {
                        $url = $this->getBaseUrl(UrlType::WEB, $secure) . $this->filesystem->getUri(Path::STATIC_VIEW);
                    }
                    break;
                case UrlType::MEDIA:
                    //$url = $this->_getMediaScriptUrl($this->filesystem, $secure);
//                    if (!$url) {
                    $path = $secure ? self::XML_PATH_SECURE_BASE_MEDIA_URL : self::XML_PATH_UNSECURE_BASE_MEDIA_URL;
                    $url = $this->getConfig($path);
                    if (!$url) {
                        $url = $this->getBaseUrl(UrlType::WEB, $secure)
                            . $this->filesystem->getUri(Path::MEDIA);
                    }
//                    }
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid base url type');
            }
            if ($url && str_contains($url, self::BASE_URL_PLACEHOLDER)) {
                $url = \str_replace(self::BASE_URL_PLACEHOLDER, $this->request->getDistroBaseUrl(), $url);
            }
            $this->baseUrlCache[$cacheKey] = $url;
        }

        return $this->baseUrlCache[$cacheKey];
    }

    public function getCurrentUrl(): string
    {
        $requestString = $this->escaper->escapeUrl(\ltrim($this->request->getRequestString(), '/'));
        return $requestString;
    }

    /**
     * Returns whether url forming scheme prepends url path with store view code
     *
     * @return boolean
     */
    public function isUseSegmentCodeInUrl(): bool
    {
        return false;
//        return !($this->hasDisableStoreInUrl() && $this->getDisableStoreInUrl())
//            && !$this->getConfig(StoreManager::XML_PATH_SINGLE_STORE_MODE_ENABLED)
//            && $this->getConfig(self::XML_PATH_STORE_IN_URL);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Retrieve store configuration data
     *
     * @param string $path
     * @return  string|null
     */
    public function getConfig(string $path): string|null
    {
        $data = $this->config->getValue($path, $this->getId());
        if ($data === null) {
            $data = $this->config->getValue($path);
        }
        return $data === false ? null : $data;
    }

    public function getScopeType(): ScopeType
    {
        return ScopeType::SEGMENT;
    }

    public function getScopeTypeName(): string
    {
        return 'Segment View';
    }

    public function getName(): string
    {
        return $this->_getData('name');
    }

    public function isUrlSecure(): bool
    {
        return $this->isFrontUrlSecure();
    }

    /**
     * Check if frontend URLs should be secure
     *
     * @return bool
     */
    public function isFrontUrlSecure(): bool
    {
        if ($this->_isFrontSecure === null) {
            $this->_isFrontSecure = $this->config
                ->isSetFlag(self::XML_PATH_SECURE_IN_FRONTEND, $this->getId());
        }
        return $this->_isFrontSecure;
    }

    /**
     * Add store code to url in case if it is enabled in configuration
     *
     * @param string $url
     * @return  string
     */
    protected function updatePathUseSegmentCode(string $url): string
    {
        if ($this->isUseSegmentCodeInUrl()) {
            // TODO: make it possible to use a config value instead of url path for a segment
            $url .= $this->config->getValue('url_key', $this->id) . '/';
        }
        return $url;
    }
}
