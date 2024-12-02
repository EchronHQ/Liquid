<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Core\Helper\DataMapper;

class PostDefinition extends AbstractViewableEntity
{
    public const TITLE_SUFFIX = ' | Attlaz';
    public string|null $publisher = null;
    public PostAuthor|null $author = null;
    public \DateTime|null $publishDate = null;
    public \DateTime|null $modifiedDate = null;
    public string|null $image = null;
    public string $intro;
    public string|null $categoryId = null;
    public array $tagIds = [];
    public int $readDuration = 5;
    public bool $draft = true;
    protected string $controllerEndpoint = 'blog/post/view/post-id';

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

        $article->draft = $dataMapper->getBooleanProperty('draft', true);

        parent::appendData($article, $dataMapper);
        $article->urlRewrites[] = 'blog/' . $article->urlKey;
        //        $notUsedProperties = $dataMapper->getNotUsedProperties();
        //        foreach ($notUsedProperties as $notUsedProperty) {
        //
        //        }

        return $article;
    }

    public function getSeoTitle(): string
    {
        return $this->metaTitle . self::TITLE_SUFFIX;
    }
}
