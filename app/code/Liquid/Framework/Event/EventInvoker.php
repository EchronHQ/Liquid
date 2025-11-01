<?php
declare(strict_types=1);

namespace Liquid\Framework\Event;

use Liquid\Framework\App\AppMode;
use Liquid\Framework\App\State;
use Liquid\Framework\Event\Config\EventListenerData;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class EventInvoker
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager,
        private readonly State                  $appState,
        private readonly LoggerInterface        $logger
    )
    {

    }

    /**
     * Dispatch event
     *
     * @param EventListenerData $configuration
     * @param Event $event
     * @return void
     */
    public function dispatch(EventListenerData $configuration, Event $event): void
    {
        if ($configuration->isDisabled) {
            return;
        }
        $object = $this->createObserver($configuration);
        if ($object !== null) {
            $this->callObserverMethod($object, $event);
        }
    }

    private function createObserver(EventListenerData $configuration): ObserverInterface|null
    {
        if ($configuration->isInstanceShared) {
            $object = $this->objectManager->get($configuration->className);
        } else {
            $object = $this->objectManager->create($configuration->className);
        }
        if ($object instanceof ObserverInterface) {
            return $object;
        }
        if ($this->appState->getMode() === AppMode::Develop) {
            throw new \LogicException(
                \sprintf(
                    'Observer "%s" must implement interface "%s"',
                    \get_class($object),
                    ObserverInterface::class
                )
            );
        }
        $this->logger->warning(\sprintf(
            'Observer "%s" must implement interface "%s"',
            \get_class($object),
            ObserverInterface::class
        ));
        return null;


    }

    /**
     * Execute Observer.
     *
     * @param ObserverInterface $object
     * @param Event $event
     * @return $this
     * @throws \LogicException
     */
    protected function callObserverMethod(ObserverInterface $object, Event $event): self
    {
        $object->execute($event);
        return $this;
    }
}
