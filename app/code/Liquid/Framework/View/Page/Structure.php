<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Page;

class Structure
{
    /**
     * Information assets elements on page
     *
     * @var array
     */
    protected array $assets = [];
    /**
     * List asset which will be removed
     *
     * @var array
     */
    protected array $removeAssets = [];
    /**
     * @var string
     */
    protected string $title = '';
    /**
     * @var string[]
     */
    protected array $metadata = [];
    /**
     * @var array
     */
    protected array $elementAttributes = [];
    /**
     * @var array
     */
    protected array $removeElementAttributes = [];
    /**
     * @var array
     */
    protected array $bodyClasses = [];
    /**
     * @var bool
     */
    protected bool $isBodyClassesDeleted = false;
    /**
     * Map of class properties.
     *
     * @var array
     */
    private array $serializableProperties = [
        'assets',
        'removeAssets',
        'title',
        'metadata',
        'elementAttributes',
        'removeElementAttributes',
        'bodyClasses',
        'isBodyClassesDeleted',
    ];

    /**
     * @param string $element
     * @param string $attributeName
     * @param string $attributeValue
     * @return $this
     */
    public function setElementAttribute(string $element, string $attributeName, string $attributeValue): self
    {
        if (empty($attributeValue)) {
            $this->removeElementAttributes[$element][] = $attributeName;
        } else {
            $this->elementAttributes[$element][$attributeName] = (string)$attributeValue;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function processRemoveElementAttributes(): self
    {
        foreach ($this->removeElementAttributes as $element => $attributes) {
            foreach ($attributes as $attributeName) {
                unset($this->elementAttributes[$element][$attributeName]);
            }
            if (empty($this->elementAttributes[$element])) {
                unset($this->elementAttributes[$element]);
            }
        }
        $this->removeElementAttributes = [];
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setBodyClass(string $value): self
    {
        if (empty($value)) {
            $this->isBodyClassesDeleted = true;
        } else {
            $this->bodyClasses[] = $value;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getBodyClasses(): array
    {
        return $this->isBodyClassesDeleted ? [] : $this->bodyClasses;
    }

    /**
     * @return array
     */
    public function getElementAttributes(): array
    {
        return $this->elementAttributes;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param string $name
     * @param string $content
     * @return $this
     */
    public function setMetadata(string $name, string $content): self
    {
        $this->metadata[$name] = (string)$content;
        return $this;
    }

    /**
     * @param string $name
     * @param array $attributes
     * @return $this
     */
    public function addAssets(string $name, array $attributes): self
    {
        $this->assets[$name] = $attributes;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeAssets(string $name): self
    {
        $this->removeAssets[$name] = $name;
        return $this;
    }

    /**
     * @return $this
     */
    public function processRemoveAssets(): self
    {
        $this->assets = array_diff_key($this->assets, $this->removeAssets);
        $this->removeAssets = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * Reformat 'Page config structure' to array.
     *
     * @return array
     * @since 101.0.0
     */
    public function __toArray(): array
    {
        $result = [];
        foreach ($this->serializableProperties as $property) {
            $result[$property] = $this->{$property};
        }

        return $result;
    }

    /**
     * Update 'Page config structure' data.
     *
     * @param array $data
     * @return void
     */
    public function populateWithArray(array $data): void
    {
        foreach ($this->serializableProperties as $property) {
            $this->{$property} = $this->getArrayValueByKey($property, $data);
        }
    }

    /**
     * Get value from array by key.
     *
     * @param string $key
     * @param array $array
     * @return array
     */
    private function getArrayValueByKey(string $key, array $array): array
    {
        return $array[$key] ?? [];
    }
}
