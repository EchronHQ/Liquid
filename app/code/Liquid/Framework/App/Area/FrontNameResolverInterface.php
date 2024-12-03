<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Area;

interface FrontNameResolverInterface
{
    /**
     * Retrieve front name
     *
     * @param bool $checkHost if true, return front name only if it is valid for the current host
     * @return string|null
     */
    public function getFrontName(bool $checkHost = false): string|null;
}
