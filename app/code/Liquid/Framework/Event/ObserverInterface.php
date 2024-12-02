<?php
declare(strict_types=1);

namespace Liquid\Framework\Event;

interface ObserverInterface
{
    public function execute(Event $observer);
}
