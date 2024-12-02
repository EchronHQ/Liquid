<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout;

use Liquid\Framework\Simplexml\XmlElement;
use Liquid\Framework\View\Layout\Reader\Context;
use Liquid\Framework\View\Layout\Reader\ReaderInterface;

class ReaderPool implements ReaderInterface
{


    /**
     * @var ReaderInterface[]
     */
    protected array $nodeReaders = [];


    /**
     * Constructor
     *
     * @param ReaderFactory $readerFactory
     * @param string[] $readers
     */
    public function __construct(
        private readonly ReaderFactory $readerFactory,
        private readonly array         $readers = []
    )
    {

    }

    /**
     * Traverse through all nodes
     *
     * @param Context $readerContext
     * @param XmlElement $element
     * @return $this
     */
    public function interpret(Context $readerContext, XmlElement $element): self
    {
        $this->prepareReader($this->readers);
        /** @var $node LayoutElement */
        foreach ($element as $node) {
            $nodeName = $node->getName();
            if (!isset($this->nodeReaders[$nodeName])) {
                continue;
            }
            /** @var $reader ReaderInterface */
            $reader = $this->nodeReaders[$nodeName];
            $reader->interpret($readerContext, $node, $element);
        }
        return $this;
    }

    /**
     * Register supported nodes and readers
     *
     * @param string[] $readers
     * @return void
     */
    protected function prepareReader(array $readers): void
    {
        if (empty($this->nodeReaders)) {
            /** @var $reader ReaderInterface */
            foreach ($readers as $readerClass) {
                $reader = $this->readerFactory->create($readerClass);
                $this->addReader($reader);
            }
        }
    }

    /**
     * Add reader to the pool
     *
     * @param ReaderInterface $reader
     * @return $this
     */
    public function addReader(ReaderInterface $reader): self
    {
        foreach ($reader->getSupportedNodes() as $nodeName) {
            $this->nodeReaders[$nodeName] = $reader;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public function getSupportedNodes(): array
    {
        return array_keys($this->nodeReaders);
    }
}
