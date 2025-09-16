<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Storage;

use Liquid\Framework\App\Entity\EntityResolverInterface;
use Liquid\UrlRewrite\Model\Resource\UrlRewriteType;
use Liquid\UrlRewrite\Model\UrlFinderInterface;

class UrlRewrite extends AbstractUrlRewriteStorage implements UrlFinderInterface
{
    private array|null $loadedUrlRewrites = null;

    public function __construct(
        private readonly EntityResolverInterface $entityResolver
    )
    {

    }


    /**
     * @return \Liquid\UrlRewrite\Model\Resource\UrlRewrite[]
     * @throws \Exception
     */
    protected function getUrlRewrites(): array
    {
        if ($this->loadedUrlRewrites === null) {


            $urlRewrites = [];
            $viewableEntities = $this->entityResolver->getEntities();
            foreach ($viewableEntities as $viewableEntity) {
                $entityUrlRewrites = $viewableEntity->getUrlRewrites();
                if (count($entityUrlRewrites) > 0) {
                    foreach ($entityUrlRewrites as $entityUrlRewrite) {
                        if (!is_string($entityUrlRewrite)) {
                            var_dump($entityUrlRewrite);
                        }

                        $urlRewrite = new \Liquid\UrlRewrite\Model\Resource\UrlRewrite();
                        $urlRewrite->setEntityId($viewableEntity->id);
                        $urlRewrite->setEntityType(get_class($viewableEntity));
                        $urlRewrite->setTargetPath($viewableEntity->getViewRoute());
                        $urlRewrite->setRedirectType(UrlRewriteType::INTERNAL);
                        $urlRewrite->setRequestPath($entityUrlRewrite);

                        $urlRewrites[] = $urlRewrite;//new \Liquid\UrlRewrite\Model\Resource\UrlRewrite($entityUrlRewrite, $viewableEntity->getViewRoute(), UrlRewriteType::INTERNAL);
                    }
                }
            }
            $this->loadedUrlRewrites = $urlRewrites;
        }
        return $this->loadedUrlRewrites;
    }
}
