<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;
/**
 * This ScopeInterface adds URL methods to the scope interface to help determine scope based on URLs.
 */
interface ScopeInterface extends \Liquid\Framework\App\Scope\ScopeInterface
{
    /**
     * Retrieve base URL
     *
     * @param UrlType $type
     * @param bool|null $secure
     * @return string
     */
    public function getBaseUrl(UrlType $type = UrlType::LINK, bool|null $secure = null): string;

    /**
     * Check is URL should be secure
     *
     * @return bool
     */
    public function isUrlSecure(): bool;
}
