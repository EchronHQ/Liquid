<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class ModularSwitchFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function create(array $data = []): ModularSwitch
    {
        return $this->objectManager->create(ModularSwitch::class, $data);
    }
}
