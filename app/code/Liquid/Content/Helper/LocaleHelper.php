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
        'categorise' => ['en-uk' => 'categorise', 'en-us' => 'categorize'],
        'centralise' => ['en-uk' => 'centralise ', 'en-us' => 'centralize'],
        'colour' => ['en-uk' => 'colour', 'en-us' => 'color'],
        'colours' => ['en-uk' => 'colours', 'en-us' => 'colors'],
        'customisable' => ['en-uk' => 'customisable', 'en-us' => 'customizable'],
        'customise' => ['en-uk' => 'customise', 'en-us' => 'customize'],
        'customised' => ['en-uk' => 'customised', 'en-us' => 'customized'],
        'democratise' => ['en-uk' => 'democratise', 'en-us' => 'democratize'],
        'digitise' => ['en-uk' => 'digitise', 'en-us' => 'digitize'],
        'harmonise' => ['en-uk' => 'harmonise', 'en-us' => 'harmonize'],
        'maximise' => ['en-uk' => 'maximise', 'en-us' => 'maximize'],
        'minimise' => ['en-uk' => 'minimise', 'en-us' => 'minimize'],
        'misspelt' => ['en-uk' => 'misspelt', 'en-us' => 'misspelled'],
        'optimise' => ['en-uk' => 'optimise', 'en-us' => 'optimize'],
        'optimised' => ['en-uk' => 'optimised', 'en-us' => 'optimized'],
        'organization' => ['en-uk' => 'organisation', 'en-us' => 'organization'],
        'organizational' => ['en-uk' => 'organisational', 'en-us' => 'organizational'],
        'organizations' => ['en-uk' => 'organisations', 'en-us' => 'organizations'],
        'organize' => ['en-uk' => 'organise', 'en-us' => 'organize'],
        'personalise' => ['en-uk' => 'personalise', 'en-us' => 'personalize'],
        'personalised' => ['en-uk' => 'personalised', 'en-us' => 'personalized'],
        'prioritise' => ['en-uk' => 'prioritise', 'en-us' => 'prioritize'],
        'realisation' => ['en-uk' => 'realisation', 'en-us' => 'realization'],
        'realise' => ['en-uk' => 'realise', 'en-us' => 'realize'],
        'recognise' => ['en-uk' => 'recognise', 'en-us' => 'recognize'],
        'specialise' => ['en-uk' => 'specialise', 'en-us' => 'specialize'],
        'standardisation' => ['en-uk' => 'standardisation ', 'en-us' => 'standardization'],
        'symbolises' => ['en-uk' => 'symbolises', 'en-us' => 'symbolizes'],
        'synchronisation' => ['en-uk' => 'synchronisation ', 'en-us' => 'synchronization'],
        'synchronize' => ['en-uk' => 'synchronise', 'en-us' => 'synchronize'],
        'unauthorised' => ['en-uk' => 'unauthorised', 'en-us' => 'unauthorized'],
        'utilise' => ['en-uk' => 'utilise', 'en-us' => 'utilize'],
        'visualisation' => ['en-uk' => 'visualisation', 'en-us' => 'visualization'],
        'visualisations' => ['en-uk' => 'visualisations', 'en-us' => 'visualizations'],
        'visualise' => ['en-uk' => 'visualise', 'en-us' => 'visualize'],
    ];

    // ization isation
    // ise ize

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
        $defaultLocale = $this->appConfig->getLocale();
        foreach ($this->translations as $key => $translations) {
//            if (\str_contains($input, $key)) {
//                $this->logger->warning('Non translated (term) "' . $key . '" found');
//            }
            foreach ($translations as $locale => $translation) {
                // Ignore if the word is already in the default locale

                //  if ($locale !== $defaultLocale || true) {


                $occurrences = StringHelper::getOccurrences($input, $translation);

                foreach ($occurrences as $occurrence) {


                    $position = \substr($input, $occurrence - 50, 50 + \strlen($translation) + 50);
                    $this->logger->warning('Non translated (' . $key . ' - ' . $locale . ') "' . $translation . '" found at "... ' . $position . ' ..."');
                }
                // }

            }
        }


    }

}
