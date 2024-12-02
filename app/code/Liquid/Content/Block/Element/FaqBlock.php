<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Framework\View\Element\ArgumentInterface;

class FaqBlock implements ArgumentInterface
{

    private array $topics = [];

    public function addTopic(array $topic): void
    {
        $this->topics[] = $topic;
    }

    public function getTopics(): array
    {
        return $this->topics;
    }
}
