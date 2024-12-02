<?php
declare(strict_types=1);

namespace Liquid\Framework;

use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Content\Model\Segment\SegmentManager;
use Liquid\Core\Helper\IdHelper;
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
        private readonly SegmentManager          $segmentManager,
        private readonly LoggerInterface         $logger,
    )
    {
    }

    public function getPageUrl(string $pageIdentifier): string|null
    {

        $pageIdentifier = IdHelper::escapeId($pageIdentifier);

        if ($pageIdentifier === 'home') {
            return $this->getUrl();
        }
        if ($pageIdentifier === 'contact-sales') {
            // TODO: rebrand demo to 'contact-sales'
            $pageIdentifier = 'demo';
        }
        // TODO: make list with special page id leading to configuration values
        if ($pageIdentifier === 'docs') {
            return $this->segmentConfig->getValue('documentation_url');
        }
        if ($pageIdentifier === 'docs-api') {
            return $this->segmentConfig->getValue('api_reference_url');
        }
        if ($pageIdentifier === 'status') {
            return $this->segmentConfig->getValue('status_url');
        }
        if ($pageIdentifier === 'app') {
            return $this->segmentConfig->getValue('app_url');
        }

        $entity = $this->entityResolver->getEntity($pageIdentifier);
        if ($entity !== null) {
            $segment = $this->segmentManager->getSegment();

            if (!$entity->isVisibleOnFront()) {
                $this->logger->warning('Get url for not visible entity `' . $entity->id . '`');
            }
            return $segment->getBaseUrl() . '/' . $entity->getUrlPath();
//            $rewrites = $entity->getUrlRewrites();
//            if (count($rewrites) > 0) {
//                return $rewrites[0];
//            }
//            return $entity->getViewRoute();
        }


//        if (\array_key_exists($pageIdentifier, $this->pageRoutes) && !\is_null($this->pageRoutes[$pageIdentifier])) {
//            return $this->getUrl($this->pageRoutes[$pageIdentifier]);
//        }
        $debugData = [
//            'registered routes' => $this->pageRoutes,
//            'exists' => \array_key_exists($pageIdentifier, $this->pageRoutes),
//
        ];
//        if (\array_key_exists($pageIdentifier, $this->pageRoutes)) {
//            $debugData['notnull'] = !\is_null($this->pageRoutes[$pageIdentifier]);
//        }
//
        $this->logger->error('[Resolver] Unable to get entity url: entity "' . $pageIdentifier . '" not found', $debugData);


        return null;
    }

    public function getUrl(string $path = '', SegmentId|null $segmentId = null): string
    {
        if ($this->isUrl($path)) {
            return $path;
        }
        $segment = $this->segmentManager->getSegment($segmentId);


//        $defaultLocale = 'en-uk';
//        if (($locale !== null && $locale->code === $defaultLocale) || !$this->segmentConfig->hasLocales()) {
//            $locale = null;
//        }

        // TODO: is there a way to check if the url exist?
        $path = \ltrim($path, '/');
//        if ($locale === null) {
//            return $this->segmentConfig->getValue('site_url') . $path;
//        }
        return $segment->getBaseUrl() . '/' . $path;


    }

    private function isUrl(string $url): bool
    {
        return \str_starts_with($url, 'http://') || \str_starts_with($url, 'https://');
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
}
