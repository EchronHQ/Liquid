<?php
declare(strict_types=1);

namespace Liquid\Blog\Model\Storage;

use Liquid\Blog\Repository\BlogRepository;
use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Content\Model\Storage\AbstractUrlRewriteStorage;
use Liquid\UrlRewrite\Model\Resource\UrlRewriteType;
use Liquid\UrlRewrite\Model\UrlFinderInterface;

class UrlRewrite extends AbstractUrlRewriteStorage implements UrlFinderInterface
{
    private array|null $loadedUrlRewrites = null;

    public function __construct(
        private readonly BlogRepository        $blogRepository,
        private readonly TerminologyRepository $terminologyRepository,
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

                            $urlRewrite = new \Liquid\UrlRewrite\Model\Resource\UrlRewrite();
                            $urlRewrite->setEntityId($viewableEntity->id);
                            $urlRewrite->setEntityType(get_class($viewableEntity));
                            $urlRewrite->setTargetPath($viewableEntity->getViewRoute());
                            $urlRewrite->setRedirectType(UrlRewriteType::INTERNAL);
                            $urlRewrite->setRequestPath($entityUrlRewrite);

                            $urlRewrites[] = $urlRewrite;
                        }

                    }
                }
            }
            $this->loadedUrlRewrites = $urlRewrites;
        }
        return $this->loadedUrlRewrites;
    }
}
