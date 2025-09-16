<?php
declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Content\Model\Segment\SegmentManager;
use Liquid\Content\Model\ViewableEntity\Url;
use Liquid\Core\Helper\IdHelper;
use Liquid\Framework\App\Config\ScopeConfig;
use Liquid\Framework\App\Entity\EntityResolverInterface;
use Liquid\Framework\App\Helper\AbstractHelper;
use Psr\Log\LoggerInterface;

class ViewableEntity extends AbstractHelper
{
    public function __construct(
        private readonly ScopeConfig                              $segmentConfig,
        private readonly EntityResolverInterface                  $entityResolver,
        private readonly SegmentManager                           $segmentManager,
        private readonly Url $url,
        private readonly LoggerInterface                          $logger,
    )
    {
    }


    public function getUrl(string $entityIdentifier): string|null
    {
        $pageIdentifier = IdHelper::escapeId($entityIdentifier);

        if ($pageIdentifier === 'home') {
            return $this->segmentManager->getSegment()->getBaseUrl();
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
            return $this->url->getEntityUrl($entity);
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
}
