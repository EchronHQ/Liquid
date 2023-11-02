<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Layout;

use Liquid\Core\Layout;

abstract class AbstractBlock implements BlockInterface
{
    protected Layout|null $layout = null;
    protected string $nameInLayout = '';

    public function getLayout(): Layout
    {
        if (\is_null($this->layout)) {
            throw new \Exception('Layout must be defined');
        }
        return $this->layout;
    }

    public function setLayout(Layout $layout): void
    {
        $this->layout = $layout;
    }


    public function getNameInLayout(): string
    {
        return $this->nameInLayout;
    }

    public function setNameInLayout(string $name): void
    {
        if (!empty($this->nameInLayout) && $this->layout) {
            if ($name === $this->nameInLayout) {
                return;
            }
            $this->getLayout()->renameElement($this->nameInLayout, $name);
        }
        $this->nameInLayout = $name;

    }

    public function setChild(string $alias, AbstractBlock $block): void
    {
        $layout = $this->getLayout();

        $thisName = $this->getNameInLayout();
        if ($layout->getChildName($thisName, $alias)) {
            $this->unsetChild($alias);
        }
        //if ($block instanceof self) {
        $blockName = $block->getNameInLayout();
        $layout->setChild($thisName, $blockName, $alias);
        //        } else {
        //            throw new \Exception('Unable to set child: No block name');
        //        }
    }

    public function unsetChild(string $alias): void
    {
        $layout = $this->getLayout();

        $layout->unsetChild($this->getNameInLayout(), $alias);

    }

    /**
     * Data
     */
    private array $data = [];

    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function addDataValues(array $data): void
    {
        foreach ($data as $index => $value) {
            $this->setData($index, $value);
        }
    }

    public function getData(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function hasData(string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    public static function extractModuleName(string $className): string
    {
        if (!$className) {
            return '';
        }

        $namespace = substr(
            $className,
            0,
            (int)strpos($className, '\\' . 'Block' . '\\')
        );

        return str_replace(['\\'], ['_'], $namespace);
    }
}
