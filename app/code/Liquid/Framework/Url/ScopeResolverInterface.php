<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;

use Liquid\Framework\App\Area\AreaCode;

/**
 * This ScopeResolverInterface adds the ability to get the Liquid area the code is executing in.
 */
interface ScopeResolverInterface extends \Liquid\Framework\App\Scope\ScopeResolverInterface
{
    /**
     * Retrieve area code
     *
     * @return AreaCode|null
     */
    public function getAreaCode(): AreaCode|null;
}
