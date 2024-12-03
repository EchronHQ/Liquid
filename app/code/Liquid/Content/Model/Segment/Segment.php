<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Segment;

use Liquid\Framework\App\Config\SegmentConfig;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\Escaper;
use Liquid\Framework\Url;

class Segment implements SegmentInterface
{
    /**
     * A placeholder for generating base URL
     */
    public const string BASE_URL_PLACEHOLDER = '{{base_url}}';

    public SegmentId $id;
    public string $code;
    private array $baseUrlCache = [];

    public function __construct(
        private readonly SegmentConfig $config,
        private readonly Request       $request,
        private readonly Url           $url,
        private readonly Escaper       $escaper,
    )
    {
    }

    public function getId(): SegmentId
    {
        return $this->id;
    }

    public function getBaseUrl(): string
    {

        $cacheKey = 'web';
        if (!isset($this->baseUrlCache[$cacheKey])) {

            $url = $this->getConfig('web/unsecure/base_link_url');
            if ($url) {
                $url = $this->updatePathUseSegmentCode($url);
            }
            if ($url && str_contains($url, self::BASE_URL_PLACEHOLDER)) {
                $url = str_replace(self::BASE_URL_PLACEHOLDER, $this->request->getDistroBaseUrl(), $url);
            }
            $this->baseUrlCache[$cacheKey] = $url;
        }

        return $this->baseUrlCache[$cacheKey];
    }

    public function getCurrentUrl(): string
    {
        $requestString = $this->escaper->escapeUrl(ltrim($this->request->getRequestString(), '/'));
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
