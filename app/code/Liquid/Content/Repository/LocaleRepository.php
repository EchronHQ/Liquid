<?php

declare(strict_types=1);

namespace Liquid\Content\Repository;

use Liquid\Content\Model\Locale;

class LocaleRepository
{
    /**
     * @var Locale[]
     */
    private array $locales;

    public function __construct()
    {
        $enUK = new Locale();
        $enUK->code = 'en-uk';
        $enUK->langCode = 'en';
        $enUK->active = false;
        //        $enUK->openGraphCode = 'en_GB';

        $enUS = new Locale();
        $enUS->code = 'en-us';
        $enUS->langCode = 'en-us';
        $enUS->active = true;
        //        $enUS->openGraphCode = 'en_US';
        //        $enUS->active = false;

        $this->locales = [
            $enUK,
            $enUS,
        ];
    }

    /**
     * @return Locale[]
     */
    public function getAll(bool $activeOnly = false): array
    {
        if ($activeOnly) {
            return \array_filter($this->locales, static function (Locale $locale) {
                return $locale->active;
            });
        }
        return $this->locales;
    }

    public function getByCode(string $code): Locale|null
    {
        $code = \strtolower($code);
        foreach ($this->locales as $locale) {
            if ($locale->code === $code) {
                return $locale;
            }
        }
        return null;
    }

    public function getDefault(): Locale
    {
        $default = $this->getByCode('en-us');
        if ($default === null) {
            throw new \Exception('Unable to load default locale');
        }
        return $default;
    }

}
