<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Layout;

use Liquid\Framework\DataObject;
use Liquid\Framework\View\Element\BlockInterface;
use Liquid\Framework\View\Layout\Layout;

/**
 * @deprecated
 */
abstract class AbstractBlock extends DataObject implements BlockInterface
{
    protected Layout|null $layout = null;
    protected string $nameInLayout = '';

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

    public function setLayout(Layout $layout): self
    {
        $this->layout = $layout;
        return $this;
    }


}
