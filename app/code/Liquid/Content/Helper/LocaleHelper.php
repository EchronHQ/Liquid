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
    ) {
    }

    private array $translations = [
        'optimise'        => ['en-uk' => 'optimise', 'en-us' => 'optimize'],
        'optimised'       => ['en-uk' => 'optimised', 'en-us' => 'optimized'],
        'analyse'         => ['en-uk' => 'analyse', 'en-us' => 'analyze'],
        'maximise'        => ['en-uk' => 'maximise', 'en-us' => 'maximize'],
        'realise'         => ['en-uk' => 'realise', 'en-us' => 'realize'],
        'symbolises'      => ['en-uk' => 'symbolises', 'en-us' => 'symbolizes'],
        'organisation'    => ['en-uk' => 'organisation', 'en-us' => 'organization'],
        'organisations'   => ['en-uk' => 'organisations', 'en-us' => 'organizations'],
        'personalise'     => ['en-uk' => 'personalise', 'en-us' => 'personalize'],
        'recognise'       => ['en-uk' => 'recognise', 'en-us' => 'recognize'],
        'personalised'    => ['en-uk' => 'personalised', 'en-us' => 'personalized'],
        'customised'      => ['en-uk' => 'customised', 'en-us' => 'customized'],
        'organisational'  => ['en-uk' => 'organisational', 'en-us' => 'organizational'],
        'unauthorised'    => ['en-uk' => 'unauthorised', 'en-us' => 'unauthorized'],
        'customise'       => ['en-uk' => 'customise', 'en-us' => 'customize'],
        'customisable'    => ['en-uk' => 'customisable', 'en-us' => 'customizable'],
        'standardisation' => ['en-uk' => 'standardisation ', 'en-us' => 'standardization'],
        'misspelt '       => ['en-uk' => 'misspelt ', 'en-us' => 'misspelled']


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

    private function getTranslation(string $input, Locale $locale): ?string
    {
        if (\array_key_exists($input, $this->translations)) {

            $term = $this->translations[$input];

            if (\array_key_exists($locale->code, $term)) {
                return $term[$locale->code];
            }
            $this->logger->warning('Translation in "' . $locale->code . '" not found for "' . $input . '"');
        }
        return null;
    }

    public function findMissingTranslations(string $input): void
    {
        $input = HtmlHelper::removeHtml($input);
        $input = \strtolower($input);

        foreach ($this->translations as $term => $translations) {
            if (\str_contains($input, $term)) {
                $this->logger->warning('Non translated (term)"' . $term . '" found');
            }
            foreach ($translations as $locale => $translation) {

                $occurrences = StringHelper::getOccurrences($input, $translation);

                foreach ($occurrences as $occurrence) {

                    $position = \substr($input, $occurrence - 20, $occurrence + \strlen($translation) + 20);
                    $this->logger->warning('Non translated (' . $term . ' - ' . $locale . ') "' . $translation . '" found at "' . $position . '"');
                }


            }
        }


    }

}
