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
        //        $enUK->openGraphCode = 'en_GB';

        //        $enUS = new Locale();
        //        $enUS->code = 'en-us';
        //        $enUS->langCode = 'en-us';
        //        $enUS->openGraphCode = 'en_US';
        //        $enUS->active = false;

        $this->locales = [
            $enUK,
            //   $enUS
        ];
    }

    /**
     * @return Locale[]
     */
    public function getAll(): array
    {
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
        $default = $this->getByCode('en-uk');
        if ($default === null) {
            throw new \Exception('Unable to load default locale');
        }
        return $default;
    }

}
