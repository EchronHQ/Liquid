<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Element;

use Liquid\Framework\DataObject;
use Liquid\Framework\View\Layout\Layout;

abstract class AbstractBlock extends DataObject implements BlockInterface
{

    public function __construct(
        private Layout $layout,
        private string $nameInLayout,
        array          $data = []
    )
    {
        parent::__construct($data);
    }

    public function getLayout(): Layout
    {
        return $this->layout;
    }

    public function setLayout(Layout $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function setChild(string $alias, AbstractBlock $block): self
    {

        if ($this->layout === null) {
            return $this;
        }

        $thisName = $this->getNameInLayout();
        if ($this->layout->getChildName($thisName, $alias)) {
            $this->unsetChild($alias);
        }
        if ($block instanceof self) {
            $blockName = $block->getNameInLayout();
        }


        $this->layout->setChild($thisName, $blockName, $alias);
        //        } else {
        //            throw new \Exception('Unable to set child: No block name');
        //        }
        return $this;
    }

    public function getNameInLayout(): string
    {
        return $this->nameInLayout;
    }

    public function unsetChild(string $alias): self
    {
        if ($this->layout === null) {
            return $this;
        }
        $this->layout->unsetChild($this->getNameInLayout(), $alias);
        return $this;

    }

    /**
     * Retrieve child block by name
     *
     * @param string $alias
     * @return AbstractBlock|bool
     */
    public function getChildBlock(string $alias)
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return false;
        }
        $name = $layout->getChildName($this->getNameInLayout(), $alias);
        if ($name) {
            return $layout->getBlock($name);
        }
        return false;
    }

    /**
     * Retrieve child block HTML
     *
     * @param string $alias
     * @param boolean $useCache
     * @return  string
     */
    public function getChildHtml(string $alias = '', bool $useCache = true): string
    {
        $layout = $this->getLayout();
        if (!$layout) {
            return '';
        }
        $name = $this->getNameInLayout();
        $out = '';
        if ($alias) {
            $childName = $layout->getChildName($name, $alias);
            if ($childName) {
                $out = $layout->renderElement($childName, $useCache);
            }
        } else {
            foreach ($layout->getChildNames($name) as $child) {
                $out .= $layout->renderElement($child, $useCache);
            }
        }

        return $out;
    }

    public function setNameInLayout(string $name): self
    {
        if (!empty($this->nameInLayout) && $this->layout) {
            if ($name === $this->nameInLayout) {
                return $this;
            }
            $this->layout->renameElement($this->nameInLayout, $name);
        }
        $this->nameInLayout = $name;
        return $this;
    }
}
