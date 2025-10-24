<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Core\Helper\Resolver;
use Liquid\Framework\App\Config\ScopeConfig;
use Psr\Log\LoggerInterface;

class TerminologyHelper
{
    /**
     * Debug terms
     */
    public const string CONFIG_DEBUG_TERMINOLOGY = 'content/terminology/debug';
    private array|null $terms = null;

    public function __construct(
        private readonly TerminologyRepository $terminologyRepository,
        private readonly Resolver              $resolver,
        private readonly ScopeConfig           $appConfig,
        private readonly LoggerInterface       $logger
    )
    {
    }

    public function buildTerms(string $input): string
    {
        $terms = $this->getBetween($input);
        if (\count($terms[0]) === 0) {
            return $input;
        }

        [$toReplace, $foundTerms] = $terms;
        //        $toReplace = $terms[0];
        //        $foundTerms = $terms[1];
        $buildTerms = [];

        $missingTerms = [];

        foreach ($foundTerms as $foundTerm) {

            $term = $this->getTerm($foundTerm);
            if ($term === null) {
                $buildTerms[] = $foundTerm;
                $missingTerms[] = $foundTerm;
            } else {
                if ($this->appConfig->getBoolValue(self::CONFIG_DEBUG_TERMINOLOGY)) {
                    $foundTerm = StringHelper::mask($foundTerm);
                }

                $buildTerms[] = '<a href="' . $term['target'] . '" class="link term intext">' . $foundTerm . '</a>';
            }

        }

        $missingTerms = \array_unique($missingTerms);
        if (\count($missingTerms) > 0) {
            $this->logger->warning('[Terminology] Some terms are missing', ['Missing terms' => $missingTerms]);
        }

        return \str_replace($toReplace, $buildTerms, $input);
    }

    public function addTerm(string $term, string $target): void
    {
        if ($this->terms === null) {
            $this->terms = [];
        }
        $term = $this->formatTerm($term);
        $this->terms[$term] = [
            'target' => $target,
        ];
    }

    public function findMissingTerms(string $input): void
    {

        $input = HtmlHelper::removeHtml($input);
        $input = \strtolower($input);

        if ($this->terms === null) {
            $loaded = $this->loadTerms();
            if (!$loaded) {
                // TODO: log this
                return;
            }
        }

        foreach ($this->terms as $term => $data) {
            $occurrences = StringHelper::getOccurrences($input, \strtolower($term));
            foreach ($occurrences as $occurrence) {
                $surroundingText = \substr($input, $occurrence - 20, $occurrence + \strlen($term) + 20);
                $this->logger->warning('Not changed term found (' . $term . ') found at "' . $surroundingText . '"');
            }


        }


    }

    private function getBetween(string $content): array
    {
        \preg_match_all('/\{TERM}(.*?)\{\/TERM}/s', $content, $matches);

        return $matches;
    }

    private function loadTerms(): bool
    {
        $terms = $this->terminologyRepository->getAll();
        foreach ($terms as $termDefinition) {
            $this->addTerm($termDefinition->id, $this->resolver->getUrl($termDefinition->getUrlPath()));
        }
        return true;
    }

    private function getTerm(string $term): array|null
    {
        if ($this->terms === null) {
            $loaded = $this->loadTerms();
            if (!$loaded) {
                return null;
            }
        }
        $term = $this->formatTerm($term);
        return $this->terms[$term] ?? null;
    }

    private function formatTerm(string $term): string
    {
        return \str_replace([' '], ['-'], \strtolower($term));
    }
}
