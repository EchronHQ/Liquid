<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Segment;

use Liquid\Framework\App\Config\AppConfig;

class Segment
{
    public SegmentId $id;
    public string $code;
    private array $baseUrlCache = [];

    public function __construct(
        private readonly AppConfig $config
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
            $hostname = ($_SERVER['REQUEST_SCHEME'] === 'http' ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'];
            if ($hostname === false) {
                $hostname = 'http://localhost:8901';
            }
//            var_dump($hostname);
//            die('--');
            // TODO: add auto detect url to this as fallback when url is not set in config
            $url = $this->config->get('site_url', $hostname);
            $this->baseUrlCache[$cacheKey] = $this->updatePathUseSegment($url);
        }

        return $this->baseUrlCache[$cacheKey];
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

    public function getLocaleCode(): string
    {
        // TODO: further implement this (from config)
        return $this->config->get('locale', 'en-gb');
    }

    /**
     * Add store code to url in case if it is enabled in configuration
     *
     * @param string $url
     * @return  string
     */
    protected function updatePathUseSegment(string $url): string
    {
        if ($this->isUseSegmentCodeInUrl()) {
            // TODO: make it possible to use a config value instead of url path for a segment
            $url .= $this->config->get('url_key', $this->code) . '/';
        }
        return $url;
    }
}
