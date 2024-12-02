<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class ResolveThemeFilesFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function create(array $data = []): ResolveThemeFiles
    {
        return $this->objectManager->create(ResolveThemeFiles::class, $data);
    }
}
