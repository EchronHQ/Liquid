<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Layout;

class LayoutActions
{
    private array $actions = [];

    public function add(string $handleId, array $actions): void
    {
        $this->actions[$handleId] = $actions;
    }

    public function get(string $handleId): array|null
    {
        return $this->actions[$handleId] ?? null;
    }
}
