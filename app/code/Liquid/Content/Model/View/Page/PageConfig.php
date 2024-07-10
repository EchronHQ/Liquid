<?php

declare(strict_types=1);

namespace Liquid\Content\Model\View\Page;

use Liquid\Blog\Model\PostAuthor;
use Liquid\Content\Model\Resource\AbstractViewableEntity;

class PageConfig
{
    public const ELEMENT_TYPE_BODY = 'body';
    public const ELEMENT_TYPE_HTML = 'html';
    public const ELEMENT_TYPE_HEAD = 'head';

    public const BODY_ATTRIBUTE_CLASS = 'class';
    public const HTML_ATTRIBUTE_LANG = 'lang';

    public const PAGE_TYPE_WEBSITE = 'website';
    public const PAGE_TYPE_ARTICLE = 'article';

    //    private string $title = 'Attlaz default title';
    private string $description = 'Attlaz default description';
    private string $keywords = 'Attlaz';

    private string|null $publisher = null;
    public PostAuthor|null $author = null;
    private \DateTime|null $publishDate = null;
    private \DateTime|null $modifiedDate = null;

    private string $pageType = self::PAGE_TYPE_WEBSITE;

    private string $image = 'icon/icon-512x512.png';

    private array $breadCrumbPages = [];

    public function addBreadcrumb(string $name, string $url): void
    {
        $this->breadCrumbPages[] = [
            'name' => $name,
            'url' => $url,
        ];
    }

    public function getBreadcrumbs(): array
    {
        return $this->breadCrumbPages;
    }

    private AbstractViewableEntity|null $definition = null;

    public function setPageDefinition(AbstractViewableEntity $definition): void
    {
        $this->definition = $definition;
    }

    public function getPageDefinition(): AbstractViewableEntity
    {
        return $this->definition;
    }

    public function getSeoTitle(): string
    {
        return $this->definition->getSeoTitle();
    }

//    public function setSeoTitle(string $title): void
//    {
//        $this->title = $title;
//    }

    public function getSeoDescription(): string
    {
        return $this->description;
    }

    public function setSeoDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getSeoKeywords(): string
    {
        return $this->keywords;
    }

    public function setSeoKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }


    public function getPublisher(): string|null
    {
        return $this->publisher;
    }

    public function setPublisher(string|null $publisher): void
    {
        $this->publisher = $publisher;
    }

    public function getAuthor(): PostAuthor|null
    {
        return $this->author;
    }

    public function setAuthor(PostAuthor|null $author): void
    {
        $this->author = $author;
    }

    public function getPublishDate(): \DateTime|null
    {
        return $this->publishDate;
    }

    public function setPublishDate(\DateTime|null $publishDate): void
    {
        $this->publishDate = $publishDate;
    }

    public function getModifiedDate(): \DateTime|null
    {
        return $this->modifiedDate;
    }

    public function setModifiedDate(\DateTime|null $modifiedDate): void
    {
        $this->modifiedDate = $modifiedDate;
    }

    public function getPageType(): string
    {
        return $this->pageType;
    }

    public function setPageType(string $pageType): void
    {
        if (!\in_array($pageType, [self::PAGE_TYPE_WEBSITE, self::PAGE_TYPE_ARTICLE])) {
            throw new \Exception('Invalid page type');
        }
        $this->pageType = $pageType;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    private array $attributes = [];

    public function addBodyClass(string $className): void
    {
        $className = preg_replace('#[^a-z0-9-_]+#', '-', strtolower($className));
        $bodyClasses = $this->getElementAttribute(self::ELEMENT_TYPE_BODY, self::BODY_ATTRIBUTE_CLASS);
        $bodyClasses = $bodyClasses ? explode(' ', $bodyClasses) : [];
        $bodyClasses[] = $className;
        $bodyClasses = array_unique($bodyClasses);
        $this->setElementAttribute(
            self::ELEMENT_TYPE_BODY,
            self::BODY_ATTRIBUTE_CLASS,
            implode(' ', $bodyClasses)
        );
    }

    public function setElementAttribute(string $elementType, string $attribute, string $value): void
    {
        $this->assertIsValidElementType($elementType);
        $this->attributes[$elementType][$attribute] = $value;
    }

    public function getElementAttribute(string $elementType, string $attribute): string|null
    {
        $this->assertIsValidElementType($elementType);
        return $this->attributes[$elementType][$attribute] ?? null;
    }

    public function getElementAttributes(string $elementType): array
    {
        $this->assertIsValidElementType($elementType);
        return $this->attributes[$elementType] ?? [];
    }

    private function assertIsValidElementType(string $elementType): void
    {
        if (!\in_array($elementType, [self::ELEMENT_TYPE_HTML, self::ELEMENT_TYPE_HEAD, self::ELEMENT_TYPE_BODY])) {
            throw new \Exception('Invalid element');
        }
    }
}
