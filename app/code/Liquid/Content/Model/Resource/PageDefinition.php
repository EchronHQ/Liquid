<?php

declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

use Liquid\Core\Helper\DataMapper;

class PageDefinition
{
    public int|string $id;

    public string|null $urlKey = null;

    public string|null $template = null;
    public string $docCssClass;

    //    public string|null $theme = null;

    public const DEFAULT_TITLE = 'Accelerate your business';
    public const DEFAULT_DESCRIPTION = 'Accelerate your business. Connect everything, engage everywhere and automate every process.';
    public const DEFAULT_KEYWORDS = 'Attlaz, platforms, connection,integration, ecommerce,marketing, data-migration, Magento, Akeneo, Shopify,';


    public const TITLE_PREFIX = '';
    public const TITLE_SUFFIX = ' | Attlaz';

    public string $metaTitle = self::DEFAULT_TITLE;
    public string $metaDescription = self::DEFAULT_DESCRIPTION;
    public string $metaKeywords = self::DEFAULT_KEYWORDS;

    public \DateTime|null $publishDate = null;
    public \DateTime|null $modifiedDate = null;

    public PageSitemapPriority $priority = PageSitemapPriority::BASE;
    public PageSitemapChangeFrequency|null $changeFrequency = null;

    public PageStatus $status = PageStatus::ACTIVE;

    public function __construct(int|string $id)
    {
        $this->id = $id;
    }

    public static function generate(int|string $id, array $data): self
    {

        $page = new self($id);

        self::appendData($page, new DataMapper($data));
        return $page;
    }

    private static function cleanupUrlKey(string $urlKey): string
    {
        $urlKey = \strtolower($urlKey);
        // TODO: normalize characters
        return \str_replace([' '], ['-'], $urlKey);
    }

    protected static function appendData(self $definition, DataMapper $data): void
    {
        $urlKey = $data->getProperty('url_key', null);
        if (\is_null($urlKey)) {
            throw new \Exception('Url key most be defined for page `' . $definition->id . '`');
        }
        $definition->urlKey = PageDefinition::cleanupUrlKey($urlKey);

        $definition->template = $data->getProperty('template', null);
        $definition->docCssClass = $data->getProperty('doc_css_class');
        //        $definition->theme = $data->getProperty('theme', null);

        $definition->metaTitle = $data->getProperty('seo_title', self::DEFAULT_TITLE);
        if ($definition->metaTitle === self::DEFAULT_TITLE) {
            // var_dump('Meta title missing for `' . $definition->id . '`');
        }

        $definition->metaDescription = $data->getProperty('seo_description', self::DEFAULT_DESCRIPTION);
        if ($definition->metaDescription === self::DEFAULT_DESCRIPTION) {
            // var_dump('Meta description missing for `' . $definition->id . '`');
        }
        $definition->metaKeywords = $data->getProperty('seo_keywords', self::DEFAULT_KEYWORDS);

        $definition->publishDate = $data->getDateTimeProperty('published');
        $definition->modifiedDate = $data->getDateTimeProperty('modified');

        $definition->priority = $data->getUntypedProperty('priority', PageSitemapPriority::BASE);
        $definition->changeFrequency = $data->getUntypedProperty('changeFrequency');

        $definition->status = $data->getUntypedProperty('status', PageStatus::ACTIVE);

    }

    protected static function getProperty(array $data, string $key, string|null $default = ''): string|null
    {
        if (\array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $default;

    }

    public function getUrlPath(): string
    {
        if (\is_null($this->urlKey)) {
            throw new \Exception('Url key is not defined for page `' . $this->id . '`');
        }
        return $this->urlKey;
    }

    public function getSeoTitle(): string
    {
        return self::TITLE_PREFIX . $this->metaTitle . self::TITLE_SUFFIX;
    }
}
