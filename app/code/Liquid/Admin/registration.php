<?php
declare(strict_types=1);

use Liquid\Framework\Component\ComponentRegistrar;
use Liquid\Framework\Component\ComponentType;

ComponentRegistrar::register(ComponentType::Module, 'Liquid_Admin', __DIR__);
