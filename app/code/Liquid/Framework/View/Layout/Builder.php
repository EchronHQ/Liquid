<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout;

use Liquid\Core\Helper\Profiler;
use Liquid\Framework\App\Request\Request;
use Liquid\Framework\Event\Event;
use Liquid\Framework\Event\EventManager;

class Builder
{
    private bool $isBuilt = false;

    public function __construct(
        private readonly Layout       $layout,
        private readonly Profiler     $profiler,
        private readonly EventManager $eventManager,
        private readonly Request      $request
    )
    {
        $this->layout->setBuilder($this);
    }

    public function build(): Layout
    {
        if (!$this->isBuilt) {
            $this->isBuilt = true;

            $this->loadLayoutUpdates();
            $this->generateLayoutXml();
            $this->generateLayoutBlocks();
        }
        return $this->layout;
    }

    protected function loadLayoutUpdates(): self
    {
        /* dispatch event for adding handles to layout update */
        $this->eventManager->dispatch(
            'layout_load_before',
            new Event(['full_action_name' => $this->request->getPathInfo(), 'layout' => $this->layout])
        );
        /**
         * Load theme layout, check if theme has a parent (default Liquid theme)
         */
        $this->layout->getProcessor()->load();
        return $this;
    }

    protected function generateLayoutXml(): self
    {
        /* generate xml from collected text updates */
        $this->layout->generateXml();
        return $this;
    }

    protected function generateLayoutBlocks(): self
    {
        $this->profiler->profilerStart('Layout');

        /* dispatch event for adding xml layout elements */
        $this->eventManager->dispatch(
            'layout_generate_blocks_before',
            new Event(['full_action_name' => $this->request->getPathInfo(), 'layout' => $this->layout])
        );

        $this->layout->generateElements();

        $this->eventManager->dispatch(
            'layout_generate_blocks_after',
            new Event(['full_action_name' => $this->request->getPathInfo(), 'layout' => $this->layout])
        );

        $this->profiler->profilerFinish('Layout');
        return $this;
    }
}
