<?php
declare(strict_types=1);

namespace Liquid\Framework\View\Design\FileResolution\Types;

use Liquid\Framework\ObjectManager\ObjectManagerInterface;

class ResolveBasicFilesFactory
{
    public function __construct(
        private readonly ObjectManagerInterface $objectManager
    )
    {

    }

    public function create(array $data = []): ResolveBasicFiles
    {
        return $this->objectManager->create(ResolveBasicFiles::class, $data);
    }
}
