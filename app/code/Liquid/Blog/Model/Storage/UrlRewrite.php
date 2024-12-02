<?php
declare(strict_types=1);

namespace Liquid\Blog\Model\Storage;

use Liquid\Blog\Repository\BlogRepository;
use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Content\Model\Segment\SegmentId;
use Liquid\Core\Helper\RequestRewriteHelper;
use Liquid\UrlRewrite\Model\Resource\UrlRewriteType;
use Liquid\UrlRewrite\Model\UrlFinderInterface;

class UrlRewrite implements UrlFinderInterface
{
    private array|null $loadedUrlRewrites = null;

    public function __construct(
        private readonly BlogRepository        $blogRepository,
        private readonly TerminologyRepository $terminologyRepository,
    )
    {

    }

    /** @inheritdoc */
    public function findOneByRequestPath(string $requestPath, SegmentId $segmentId): \Liquid\UrlRewrite\Model\Resource\UrlRewrite|null
    {
        $urlRewrites = $this->getUrlRewrites();
        foreach ($urlRewrites as $urlRewrite) {
            $rewrite = RequestRewriteHelper::rewrite($urlRewrite, $requestPath);
            if ($rewrite !== null) {
                return $rewrite;
            }
        }

        return null;
    }

    /** @inheritdoc */
    public function findAllByRequestPath(string $requestPath, SegmentId $segmentId): array
    {
        $urlRewrites = $this->getUrlRewrites();
        $result = [];
        foreach ($urlRewrites as $urlRewrite) {
            $rewrite = RequestRewriteHelper::rewrite($urlRewrite, $requestPath);
            if ($rewrite !== null) {
                $result[] = $rewrite;
            }
        }

        return $result;
    }

    /**
     * @return \Liquid\UrlRewrite\Model\Resource\UrlRewrite[]
     * @throws \Exception
     */
    private function getUrlRewrites(): array
    {
        if ($this->loadedUrlRewrites === null) {


            $urlRewrites = [];
            $viewableEntities = $this->blogRepository->getEntities();
            $terms = $this->terminologyRepository->getEntities();

            $viewableEntities = array_merge($viewableEntities, $terms);

            foreach ($viewableEntities as $viewableEntity) {
                $entityUrlRewrites = $viewableEntity->getUrlRewrites();
                if (count($entityUrlRewrites) > 0) {
                    foreach ($entityUrlRewrites as $entityUrlRewrite) {
                        if (!is_string($entityUrlRewrite)) {
                            var_dump($entityUrlRewrite);
                        } else {
                            $urlRewrites[] = new \Liquid\UrlRewrite\Model\Resource\UrlRewrite($entityUrlRewrite, $viewableEntity->getViewRoute(), UrlRewriteType::INTERNAL);
                        }

                    }
                }
            }
            $this->loadedUrlRewrites = $urlRewrites;
        }
        return $this->loadedUrlRewrites;
    }
}
