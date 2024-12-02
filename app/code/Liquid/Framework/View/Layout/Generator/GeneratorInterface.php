<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout\Generator;

use Liquid\Framework\View\Layout\Reader\Context;

interface GeneratorInterface
{
    /**
     * Traverse through all elements of specified schedule structural elements of it
     *
     * @param Context $readerContext
     * @param GeneratorContext $generatorContext
     * @return $this
     */
    public function process(Context $readerContext, GeneratorContext $generatorContext): self;

    /**
     * Return type of generator
     *
     * @return string
     */
    public function getType(): string;
}
