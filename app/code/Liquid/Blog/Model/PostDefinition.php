<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Core\Helper\DataMapper;

class PostDefinition extends PageDefinition
{
    public string|null $publisher = null;
    public PostAuthor|null $author = null;
    public \DateTime|null $publishDate = null;
    public \DateTime|null $modifiedDate = null;
    public string|null $image = null;

    private const URL_PATH_PREFIX = 'blog';
    public const TITLE_SUFFIX = ' | Attlaz';


    public string $intro;
    public string|null $categoryId = null;
    public array $tagIds = [];

    public int $readDuration = 5;


    public static function generate(int|string $id, array $data): static
    {
        $article = new PostDefinition($id);

        $author = new PostAuthor();
        $author->name = 'Stijn Duynslaeger';

        $article->author = $author;

        $article->publisher = 'https://www.facebook.com/attlaz/';

        $dataMapper = new DataMapper($data);

        $article->intro = $dataMapper->getProperty('intro');
        $article->categoryId = $dataMapper->getProperty('category', null);
        $article->tagIds = $dataMapper->getArrayProperty('tags', []);
        $article->image = $dataMapper->getProperty('image', null);

        parent::appendData($article, $dataMapper);

        //        $notUsedProperties = $dataMapper->getNotUsedProperties();
        //        foreach ($notUsedProperties as $notUsedProperty) {
        //
        //        }

        return $article;
    }

    public function getUrlPath(): string
    {
        if ($this->urlKey === '') {
            return self::URL_PATH_PREFIX;
        }
        return self::URL_PATH_PREFIX . '/' . $this->urlKey;
    }

    public function getSeoTitle(): string
    {
        return $this->metaTitle . self::TITLE_SUFFIX;
    }
}
