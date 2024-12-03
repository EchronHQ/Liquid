<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Result;

use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Layout\Builder;
use Liquid\Framework\View\Layout\Layout;

/**
 *  A generic layout response can be used for rendering any kind of layout
 *  So it comprises a response body from the layout elements it has and sets it to the HTTP response
 */
class LayoutPage extends AbstractResult
{
    public function __construct(
        protected readonly Layout                 $layout,
        protected readonly ObjectManagerInterface $objectManager
    )
    {
        $this->initLayoutBuilder();
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

    protected function initLayoutBuilder(): void
    {
        $this->objectManager->create(Builder::class, ['layout' => $this->layout]);
    }

    protected function render(HttpResponseInterface $response): AbstractResult
    {
        $output = $this->layout->getOutput();
        $response->appendBody($output);
        return $this;
    }
}
