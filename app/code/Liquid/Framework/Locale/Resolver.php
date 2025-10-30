<?php
declare(strict_types=1);

namespace Liquid\Framework\Locale;

use Liquid\Framework\App\Config\SegmentConfigInterface;
use Liquid\Framework\App\DeploymentConfig;

class Resolver implements ResolverInterface
{
    /**
     * Resolver default locale
     */
    public const string DEFAULT_LOCALE = 'en_UK';

    /**
     * Default locale code
     */
    protected string|null $defaultLocale = null;
    /**
     * Locale code
     */
    protected string|null $locale = null;

    public function __construct(
        private readonly SegmentConfigInterface                 $segmentConfig,
        private readonly DeploymentConfig $deploymentConfig,
                                                                $locale = null,
    )
    {
        $this->setLocale($locale);
    }

    public function getDefaultLocale(): string
    {
        if (!$this->defaultLocale) {
            $locale = false;
            if ($this->deploymentConfig->isAvailable() && $this->deploymentConfig->isDbAvailable()) {
                $locale = null;// $this->segmentConfig->getValue($this->getDefaultLocalePath(), $this->scopeType);
            }
            if (!$locale) {
                $locale = self::DEFAULT_LOCALE;
            }
            $this->defaultLocale = $locale;
        }
        return $this->defaultLocale;
    }

    public function getLocale(): string
    {
        if ($this->locale === null) {
            $this->setLocale();
        }
        return $this->locale;
    }

    /**
     * @inheritdoc
     */
    public function setLocale(string|null $locale = null): self
    {
        if (is_string($locale)) {
            $this->locale = $locale;
        } else {
            $this->locale = $this->getDefaultLocale();
        }
        return $this;
    }
}
