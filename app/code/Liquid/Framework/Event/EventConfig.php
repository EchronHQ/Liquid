<?php
declare(strict_types=1);

namespace Liquid\Framework\Event;

use Liquid\Framework\Event\Config\Data;
use Liquid\Framework\Event\Config\EventListenerData;

class EventConfig
{
    public function __construct(
        private readonly Data $configData
    )
    {

    }

    /**
     * @param string $eventName
     * @return EventListenerData[]
     */
    public function getObservers(string $eventName): array
    {
        return $this->configData->get($eventName, []);
    }
}
