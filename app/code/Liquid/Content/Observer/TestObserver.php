<?php
declare(strict_types=1);

namespace Liquid\Content\Observer;

use Liquid\Framework\Event\Attribute\AsEventListener;
use Liquid\Framework\Event\Event;
use Liquid\Framework\Event\ObserverInterface;
use Liquid\Framework\View\Layout\Layout;
use Psr\Log\LoggerInterface;

#[AsEventListener('layout_load_before', name: 'Before layout load handler')]
class TestObserver implements ObserverInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    )
    {

    }

    public function execute(Event $event): void
    {
        $route = $event->getData('full_action_name');
        /** @var Layout $layout */
        $layout = $event->getData('layout');
        // TODO: this shouldn't be needed here!
        $layout->addContainer('root', 'Root');

        $this->logger->debug('Load layout before');
    }
}
