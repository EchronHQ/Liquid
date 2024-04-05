<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Content\Model\Locale;
use Liquid\Core\Model\AppConfig;
use Psr\Log\LoggerInterface;

class LocaleHelper
{
    public function __construct(
        private readonly AppConfig       $appConfig,
        private readonly LoggerInterface $logger
    )
    {
    }

    private array $translations = [
        'analyse' => ['en-uk' => 'analyse', 'en-us' => 'analyze'],
        'centralise' => ['en-uk' => 'centralise ', 'en-us' => 'centralize'],
        'customisable' => ['en-uk' => 'customisable', 'en-us' => 'customizable'],
        'customise' => ['en-uk' => 'customise', 'en-us' => 'customize'],
        'colour' => ['en-uk' => 'colour', 'en-us' => 'color'],
        'customised' => ['en-uk' => 'customised', 'en-us' => 'customized'],
        'maximise' => ['en-uk' => 'maximise', 'en-us' => 'maximize'],
        'misspelt ' => ['en-uk' => 'misspelt ', 'en-us' => 'misspelled'],
        'optimise' => ['en-uk' => 'optimise', 'en-us' => 'optimize'],
        'optimised' => ['en-uk' => 'optimised', 'en-us' => 'optimized'],
        'organization' => ['en-uk' => 'organisation', 'en-us' => 'organization'],
        'organizational' => ['en-uk' => 'organisational', 'en-us' => 'organizational'],
        'organizations' => ['en-uk' => 'organisations', 'en-us' => 'organizations'],
        'personalise' => ['en-uk' => 'personalise', 'en-us' => 'personalize'],
        'personalised' => ['en-uk' => 'personalised', 'en-us' => 'personalized'],
        'prioritise' => ['en-uk' => 'prioritise', 'en-us' => 'prioritize'],
        'realise' => ['en-uk' => 'realise', 'en-us' => 'realize'],
        'recognise' => ['en-uk' => 'recognise', 'en-us' => 'recognize'],
        'standardisation' => ['en-uk' => 'standardisation ', 'en-us' => 'standardization'],
        'symbolises' => ['en-uk' => 'symbolises', 'en-us' => 'symbolizes'],
        'unauthorised' => ['en-uk' => 'unauthorised', 'en-us' => 'unauthorized'],
        'utilise' => ['en-uk' => 'utilise ', 'en-us' => 'utilize'],
        'visualise' => ['en-uk' => 'visualise ', 'en-us' => 'visualize'],
        'visualisations ' => ['en-uk' => 'visualisations ', 'en-us' => 'visualizations'],
        'personalise' => ['en-uk' => 'personalise', 'en-us' => 'personalize'],
        'personalised' => ['en-uk' => 'personalised', 'en-us' => 'personalized'],
    ];

    private function isCapital(string $character): bool
    {
        return \strtoupper($character) === $character;
    }

    public function translate(string $input): string
    {


        $locale = $this->appConfig->getLocale();

        $translation = $this->getTranslation(\strtolower($input), $locale);


        if ($translation === null) {
            $this->logger->warning('No translation found for "' . $input . '"');
            if ($this->appConfig->debugTranslations()) {
                return '[' . StringHelper::mask($input) . ']';
            }
            return $input;
        }

        if ($this->isCapital($input[0])) {
            $translation = \ucfirst($input);
        }


        if ($this->appConfig->debugTranslations()) {
            return StringHelper::mask($translation);
        }
        return $translation;

    }

    private function getTranslation(string $input, Locale $locale): string|null
    {
        if (\array_key_exists($input, $this->translations)) {

            $term = $this->translations[$input];

            if (\array_key_exists($locale->code, $term)) {
                return $term[$locale->code];
            }
            $this->logger->warning('Translation in "' . $locale->code . '" not found for "' . $input . '"');
        }
        // Also search for translations that are already translated
        foreach ($this->translations as $key => $translations) {
            foreach ($translations as $language => $translation) {
                if ($translation === $input) {
                    return $translations[$locale->code];
                }
            }
        }
        return null;
    }

    public function findMissingTranslations(string $input): void
    {
        $input = HtmlHelper::removeHtml($input);
        $input = \strtolower($input);
        $defaultLocale = 'en-us';
        foreach ($this->translations as $key => $translations) {
//            if (\str_contains($input, $key)) {
//                $this->logger->warning('Non translated (term) "' . $key . '" found');
//            }
            foreach ($translations as $locale => $translation) {
                // Ignore if the word is already in the default locale

                if ($locale !== $defaultLocale) {


                    $occurrences = StringHelper::getOccurrences($input, $translation);

                    foreach ($occurrences as $occurrence) {

                        $position = \substr($input, $occurrence - 20, $occurrence + \strlen($translation) + 20);
                        $this->logger->warning('Non translated (' . $key . ' - ' . $locale . ') "' . $translation . '" found at "' . $position . '"');
                    }
                }

            }
        }


    }

}
