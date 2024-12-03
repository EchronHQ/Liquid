<?php
declare(strict_types=1);

namespace Liquid\Framework;

use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Content\Model\Segment\SegmentResolver;
use Liquid\Framework\App\Config\SegmentConfig;
use Liquid\Framework\App\Entity\EntityResolverInterface;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\App\State;
use Psr\Log\LoggerInterface;

class Url
{
    public function __construct(
        private readonly SegmentConfig           $segmentConfig,
        private readonly EntityResolverInterface $entityResolver,
        private readonly State                   $appState,
        private readonly Request                 $request,
        private readonly SegmentResolver         $segmentResolver,
        private readonly LoggerInterface         $logger,
    )
    {
    }

    /**
     * TODO: this needs better + remove this from url class (move it to segment class maybe?)
     * @param string $path
     * @param SegmentId|null $segmentId
     * @return string
     * @throws \Exception
     */
    public function getUrl(string $path = '', SegmentId|null $segmentId = null): string
    {
        if ($this->isUrl($path)) {
            return $path;
        }

        //  var_dump($path);

//        $this->segmentConfig->get()

        //   $this->segmentResolver->getCurrentSegmentId()
        //     $segment = $this->segmentManager->getSegment($segmentId);


//        $defaultLocale = 'en-uk';
//        if (($locale !== null && $locale->code === $defaultLocale) || !$this->segmentConfig->hasLocales()) {
//            $locale = null;
//        }

        // TODO: is there a way to check if the url exist?
        $path = \ltrim($path, '/');
//        if ($locale === null) {
        return $this->segmentConfig->getValue('web/unsecure/base_url') . $path;
//        }
        //  return $segment->getBaseUrl() . '/' . $path;
        return '';

    }

    /**
     * Retrieve current url
     *
     * @return string
     */
    public function getCurrentUrl(): string
    {
        $httpHostWithPort = $this->request->getHttpHost(false);
        $httpHostWithPort = explode(':', $httpHostWithPort);
        $httpHost = $httpHostWithPort[0] ?? '';
        $port = '';
        if (isset($httpHostWithPort[1])) {
            $defaultPorts = [
                Request::DEFAULT_HTTP_PORT,
                Request::DEFAULT_HTTPS_PORT,
            ];
            /** Only add custom port to url when it's not a default one */
            if (!in_array($httpHostWithPort[1], $defaultPorts, true)) {
                $port = ':' . $httpHostWithPort[1];
            }
        }
        return $this->request->getScheme() . '://' . $httpHost . $port . $this->request->getRequestUri();
    }

    private function isUrl(string $url): bool
    {
        return \str_starts_with($url, 'http://') || \str_starts_with($url, 'https://');
    }
}
