<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Core\Helper\DataMapper;

class CategoryDefinition extends PageDefinition
{
    public string $title_long = '';

    public const TITLE_PREFIX = 'Blog - ';

    public static function generate(int|string $id, array $data): self
    {
        $article = new CategoryDefinition($id);

        self::appendData($article, new DataMapper($data));

        return $article;
    }

    protected static function appendData(self|PageDefinition $definition, DataMapper $data): void
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
