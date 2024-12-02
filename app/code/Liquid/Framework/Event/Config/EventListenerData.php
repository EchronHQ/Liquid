<?php
declare(strict_types=1);

namespace Liquid\Framework\Event\Config;

class EventListenerData
{
    public string $name;
    public string $className;
    public string $eventId;

    public bool $isInstanceShared;
    public bool $isDisabled;
}
