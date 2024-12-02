<?php
declare(strict_types=1);

namespace Liquid\Framework\Event\Attribute;

use Echron\Tools\StringHelper;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AsEventListener
{

    private string $name;

    /**
     * @param string $eventId The event name to listen to
     * @param string|null $name
     * @throws \Exception
     */
    public function __construct(
        private readonly string $eventId,
        string                  $name = null,
        private readonly bool   $isDisabled = false,
        private readonly bool   $isInstanceShared = true
    )
    {
        $this->name = $name ?? $eventId . '-' . StringHelper::generateRandom(8);
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    public function isInstanceShared(): bool
    {
        return $this->isInstanceShared;
    }
}
