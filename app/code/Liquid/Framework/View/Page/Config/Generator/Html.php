<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Page\Config\Generator;

use Liquid\Framework\View\Layout\Generator\GeneratorContext;
use Liquid\Framework\View\Layout\Generator\GeneratorInterface;
use Liquid\Framework\View\Layout\Reader\Context;

class Html implements GeneratorInterface
{

    public function process(Context $readerContext, GeneratorContext $generatorContext): GeneratorInterface
    {
        // TODO: Implement process() method.
    }

    public function getType(): string
    {
        // TODO: Implement getType() method.
    }
}
