<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Core\Helper\DataMapper;

class TagDefinition extends AbstractViewableEntity
{
    public string $title_long = '';
    protected string $controllerEndpoint = 'blog/tag/view/tag-id/:entity-id';

    public static function generate(int|string $id, array $data): static
    {
        $article = new TagDefinition($id);

        self::appendData($article, new DataMapper($data));
        $article->urlRewrites[] = 'blog/tag/' . $article->urlKey;
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


}
