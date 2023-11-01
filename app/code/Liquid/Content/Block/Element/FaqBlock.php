<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Content\Block\TemplateBlock;

class FaqBlock extends TemplateBlock
{
    protected string|null $template = 'block/faq.phtml';

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
