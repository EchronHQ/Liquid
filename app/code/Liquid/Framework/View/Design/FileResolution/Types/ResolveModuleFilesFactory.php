<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class ResolveModuleFilesFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function create(array $data = []): ResolveModuleFiles
    {
        return $this->objectManager->create(ResolveModuleFiles::class, $data);
    }
}
