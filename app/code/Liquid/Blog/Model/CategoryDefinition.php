<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Core\Helper\DataMapper;

class CategoryDefinition extends AbstractViewableEntity
{
    public const string TITLE_PREFIX = 'Blog - ';
    public string $title_long = '';
    protected string $controllerEndpoint = 'blog/category/view/category-id/:entity-id';

    public static function generate(int|string $id, array $data): static
    {
        $article = new CategoryDefinition($id);

        self::appendData($article, new DataMapper($data));
        $article->urlRewrites[] = 'blog/category/' . $article->urlKey;
        $article->docCssClass = 'theme--light palette--pomogranate accent--purple';
        return $article;
    }

    protected static function appendData(self|AbstractViewableEntity $definition, DataMapper $data): void
    {
        parent::appendData($definition, $data);
        if ($definition instanceof self) {
            $definition->title_long = $data->getProperty('title_long', null);
        }

        $data->report();
    }

    public function getSeoTitle(): string
    {
        return self::TITLE_PREFIX . $this->metaTitle . self::TITLE_SUFFIX;
    }
}
