<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout\Generator;

use Liquid\Framework\View\Layout\Data\LayoutDataStructure;
use Liquid\Framework\View\Layout\Layout;

class GeneratorContext
{

    public function __construct(
        private readonly LayoutDataStructure $structure,
        private readonly Layout              $layout
    )
    {

    }

    public function getStructure(): LayoutDataStructure
    {
        return $this->structure;
    }

    public function getLayout(): Layout
    {
        return $this->layout;
    }
}
