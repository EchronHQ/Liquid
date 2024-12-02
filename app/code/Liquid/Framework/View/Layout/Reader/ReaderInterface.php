<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout\Reader;

use Liquid\Framework\Simplexml\XmlElement;

interface ReaderInterface
{
    /**
     * Read children elements structure and fill scheduled structure
     *
     * @param Context $readerContext
     * @param XmlElement $element
     * @return $this
     */
    public function interpret(Context $readerContext, XmlElement $element): self;

    /**
     * Get nodes types that current reader is support
     *
     * @return string[]
     */
    public function getSupportedNodes(): array;
}
