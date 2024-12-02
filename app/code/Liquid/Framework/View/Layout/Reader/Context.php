<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout\Reader;

use Liquid\Framework\View\Layout\ScheduledStructure;
use Liquid\Framework\View\Page\Structure;

class Context
{
    public function __construct(
        private readonly Structure          $pageConfigStructure,
        private readonly ScheduledStructure $scheduledStructure,
    )
    {

    }

    public function getPageConfigStructure(): Structure
    {
        return $this->pageConfigStructure;
    }

    public function getScheduledStructure(): ScheduledStructure
    {
        return $this->scheduledStructure;
    }
}
