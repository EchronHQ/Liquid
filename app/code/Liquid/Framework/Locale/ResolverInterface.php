<?php
declare(strict_types=1);

namespace Liquid\Framework\Locale;

interface ResolverInterface
{
    /**
     * Retrieve default locale code
     *
     * @return string
     */
    public function getDefaultLocale(): string;

    /**
     * Set locale
     *
     * @param string|null $locale
     * @return  self
     */
    public function setLocale(string|null $locale = null): self;

    /**
     * Retrieve locale
     *
     * @return string
     */
    public function getLocale(): string;
}
