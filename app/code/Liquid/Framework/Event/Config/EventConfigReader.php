<?php
declare(strict_types=1);

namespace Liquid\Framework\Event\Config;

use Liquid\Framework\Config\Reader\AttributeConfigReader;
use Liquid\Framework\Event\Attribute\AsEventListener;
use Liquid\Framework\Module\File\Dir;
use Liquid\Framework\Module\ModuleHelper;
use Psr\Log\LoggerInterface;

class EventConfigReader extends AttributeConfigReader
{

    public function __construct(
        ModuleHelper    $modulesList,
        Dir             $moduleDir,
        LoggerInterface $logger
    )
    {
        parent::__construct(
            $modulesList,
            $moduleDir,
            $logger,
            AsEventListener::class,
            Dir::MODULE_OBSERVER_DIR
        );
    }

    /**
     * @return EventListenerData[]
     * @throws \ReflectionException
     */
    public function read(string|null $scope = null): array
    {
        /** @var AsEventListener[] $values */
        $values = parent::read($scope);

        $eventListeners = [];
        foreach ($values as $class => $value) {
            $eventListenerData = $this->convertEventListenerData($class, $value);

            $eventId = mb_strtolower($eventListenerData->eventId);
            $listeners = $eventListeners[$eventId] ?? [];

            $listeners[] = $eventListenerData;
            $eventListeners[$eventId] = $listeners;
        }
        return $eventListeners;
    }

    private function convertEventListenerData(string $className, AsEventListener $asEventListener): EventListenerData
    {
        $eventListenerData = new EventListenerData();

        $eventListenerData->className = $className;
        $eventListenerData->eventId = $asEventListener->getEventId();
        $eventListenerData->name = $asEventListener->getName();
        $eventListenerData->isDisabled = $asEventListener->isDisabled();
        $eventListenerData->isInstanceShared = $asEventListener->isInstanceShared();

        return $eventListenerData;
    }
}
