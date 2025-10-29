<?php
declare(strict_types=1);

namespace Liquid\Content\Model\Resource;

use Liquid\Core\Helper\DataMapper;
use Liquid\Core\Helper\IdHelper;

abstract class AbstractViewableEntity
{
    public const string ID_PREFIX = '';
    public const string DEFAULT_TITLE = 'Accelerate your business';
    public const string DEFAULT_DESCRIPTION = 'Accelerate your business. Connect everything, engage everywhere and automate every process.';
    public const string DEFAULT_KEYWORDS = 'Attlaz, platforms, connection,integration, ecommerce,marketing, data-migration, Magento, Akeneo, Shopify,';
    public const TITLE_PREFIX = '';

    //    public string|null $theme = null;
    public const TITLE_SUFFIX = ' | Attlaz';
    public int|string $id;
    public string|null $urlKey = null;
    public string|null $template = null;
    public string $docCssClass;
    public string $metaTitle = self::DEFAULT_TITLE;
    public string $metaDescription = self::DEFAULT_DESCRIPTION;
    public string $metaKeywords = self::DEFAULT_KEYWORDS;

    public \DateTime|null $publishDate = null;
    public \DateTime|null $modifiedDate = null;

    public PageSitemapPriority $priority = PageSitemapPriority::BASE;
    public PageSitemapChangeFrequency|null $changeFrequency = null;

    public PageStatus $status = PageStatus::ACTIVE;
    /** @var string[] */
    protected array $urlRewrites = [];

    protected string $controllerEndpoint = 'content/page/view/page-id/:entity-id';
    private string|null $requestPath = null;

    public function __construct(int|string $id)
    {
        $this->id = self::generateId($id);
    }

    public static function generateId(string $id): string
    {
        return self::_generateId($id, self::ID_PREFIX);
    }

    public static function generate(int|string $id, array $data): static
    {

        $page = new static(self::generateId($id));
        self::appendData($page, new DataMapper($data));

        // Validate that doc_css_class as theme, palette and accent variables
        $classes = \explode(' ', $page->docCssClass);
        $hasTheme = false;
        $hasPalette = false;
        $hasAccent = false;
        foreach ($classes as $class) {
            if (str_starts_with($class, 'theme--')) {
                $hasTheme = true;
            }
            if (str_starts_with($class, 'palette--')) {
                $hasPalette = true;
            }
            if (str_starts_with($class, 'accent--')) {
                $hasAccent = true;
            }
        }
        if (!$hasTheme) {
            var_dump($classes);
            throw new \Error('Theme is missing for `' . $id . '`');
        }
        if (!$hasPalette) {
            var_dump($classes);
            throw new \Error('Palette is missing for `' . $id . '`');
        }
        if (!$hasAccent) {
            var_dump($classes);
            throw new \Error('Accent is missing for `' . $id . '`');
        }

        return $page;
    }

    final protected static function _generateId(string $id, string $prefix): string
    {
        $id = IdHelper::escapeId($id);
        return $prefix === '' ? $id : $prefix . '-' . $id;
    }

    protected static function appendData(self $definition, DataMapper $data): void
    {
        $urlKey = $data->getProperty('url_key', null);
        if ($urlKey === null) {
            throw new \Exception('Url key most be defined for page `' . $definition->id . '`');
        }
        $definition->urlKey = self::cleanupUrlKey($urlKey);

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

        $definition->urlRewrites = $data->getArrayProperty('urlRewrites', []);

    }

    protected static function getProperty(array $data, string $key, string|null $default = ''): string|null
    {
        if (\array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $default;

    }

    private static function cleanupUrlKey(string $urlKey): string
    {
        $urlKey = \strtolower($urlKey);
        // TODO: normalize characters
        return \str_replace([' '], ['-'], $urlKey);
    }

    public function getUrlPath(): string
    {
        if (\count($this->urlRewrites) > 0) {
            return $this->urlRewrites[0];
        }
        return $this->getViewRoute();
    }

    /**
     * This is the route which displays the entity,
     * mainly used to know where url rewrites point to
     *
     * @return string
     */
    public function getViewRoute(): string
    {
        return \str_replace(':entity-id', $this->id, $this->controllerEndpoint);
    }

    public function getSeoTitle(): string
    {
        return self::TITLE_PREFIX . $this->metaTitle . self::TITLE_SUFFIX;
    }

    /**
     * @return string[]
     */
    public function getUrlRewrites(): array
    {
        return $this->urlRewrites;
    }

    /**
     * @param string[] $urlRewrites
     * @return void
     */
    public function setUrlRewrites(array $urlRewrites): void
    {
        $this->urlRewrites = $urlRewrites;
    }

    public function isVisibleOnFront(): bool
    {
        return $this->status === PageStatus::ACTIVE;
    }

    public function setControllerEndpoint(string $controllerEndpoint): void
    {
        $this->controllerEndpoint = $controllerEndpoint;
    }

    public function getRequestPath(): string|null
    {
        return $this->requestPath;
    }

    public function setRequestPath(string|null $value): self
    {
        $this->requestPath = $value;
        return $this;
    }
}
