<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Core\Helper\DataMapper;

class TagDefinition extends PageDefinition
{
    public string $title_long = '';

    public static function generate(int|string $id, array $data): self
    {
        $article = new TagDefinition($id);

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
        return 'blog/tag/' . $this->urlKey;
    }
}
