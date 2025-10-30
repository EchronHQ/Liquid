<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Core\Helper\DataMapper;

class PostDefinition extends AbstractViewableEntity
{
    public const string TITLE_SUFFIX = ' | Attlaz';
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
    public array|null $anchors = null;
    protected string $controllerEndpoint = 'blog/post/view/post-id/:entity-id';

    public static function generate(int|string $id, array $data): static
    {
        $article = new PostDefinition($id);

        $author = new PostAuthor();
        if (isset($data['author']) && $data['author'] === 'charlottegrigg') {
            $author->name = 'Charlotte Grigg';
            $author->role = 'Product Marketing Manager';
            $author->link = '';
        } else {
            $author->name = 'Stijn Duynslaeger';
            $author->role = 'CEO';
            $author->link = '';
        }


        $article->author = $author;

        $article->publisher = 'https://www.facebook.com/attlaz/';

        $dataMapper = new DataMapper($data);
        parent::appendData($article, $dataMapper);


        $article->intro = $dataMapper->getProperty('intro');
        $article->categoryId = $dataMapper->getProperty('category', null);
        $article->tagIds = $dataMapper->getArrayProperty('tags', []);
        $article->image = $dataMapper->getProperty('image', null);

        $article->draft = $dataMapper->getBooleanProperty('draft', true);


        $article->docCssClass = 'theme--light palette--chroma accent--cyan';

        $article->anchors = $dataMapper->getArrayProperty('anchors', null);

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
