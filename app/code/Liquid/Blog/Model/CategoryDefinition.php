<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Core\Helper\DataMapper;

class CategoryDefinition extends AbstractViewableEntity
{
    public string $title_long = '';

    public const TITLE_PREFIX = 'Blog - ';

    public static function generate(int|string $id, array $data): static
    {
        $article = new CategoryDefinition($id);

        self::appendData($article, new DataMapper($data));

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


    public function getUrlPath(): string
    {
        return 'blog/category/' . $this->urlKey;
    }

    public function getSeoTitle(): string
    {
        return self::TITLE_PREFIX . $this->metaTitle . self::TITLE_SUFFIX;
    }
}
