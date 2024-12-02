<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Result;

use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\Result;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Layout\Builder;
use Liquid\Framework\View\Layout\Layout;

class LayoutPage extends Result
{
    public function __construct(
        protected readonly Layout                 $layout,
        protected readonly ObjectManagerInterface $objectManager
    )
    {
        $this->initLayoutBuilder();
    }

    protected function initLayoutBuilder(): void
    {
        $this->objectManager->create(Builder::class, ['layout' => $this->layout]);
    }

    /**
     * @param string $handleName
     * @return $this
     */
    public function addHandle(string $handleName): self
    {
        $this->layout->getProcessor()->addHandle($handleName);
        return $this;
    }

    protected function render(HttpResponseInterface $response): Result
    {
        $output = $this->layout->getOutput();
        $response->appendBody($output);
        return $this;
    }
}
