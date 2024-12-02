<?php
declare(strict_types=1);

namespace Liquid\Framework\Event;

use Liquid\Core\Helper\Profiler;

class EventManager
{
    public function __construct(
        private readonly Profiler     $profiler,
        private readonly EventConfig  $eventConfig,
        private readonly EventInvoker $eventInvoker
    )
    {
    }

    public function dispatch(string $eventName, Event $event): void
    {
        $eventName = mb_strtolower($eventName);

        $this->profiler->profilerStart('EVENT:' . $eventName, ['group' => 'EVENT', 'name' => $eventName]);
        foreach ($this->eventConfig->getObservers($eventName) as $observerConfig) {
            $this->profiler->profilerStart('OBSERVER:' . $observerConfig->name);
            $this->eventInvoker->dispatch($observerConfig, $event);
            $this->profiler->profilerFinish('OBSERVER:' . $observerConfig->name);
        }
        $this->profiler->profilerFinish('EVENT:' . $eventName);
    }
}
