<?php
declare(strict_types=1);

use Liquid\Framework\Component\ComponentRegistrar;
use Liquid\Framework\Component\ComponentType;

ComponentRegistrar::register(ComponentType::Theme, 'Liquid_Default', __DIR__);
