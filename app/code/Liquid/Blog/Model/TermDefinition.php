<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Core\Helper\DataMapper;

class TermDefinition extends PageDefinition
{
    public string $term;
    public string $termLong;
    private array $useCaseCategoryIds = [];

    private const URL_PATH_PREFIX = 'blog/term';

    public static function generate(int|string $id, array $data): static
    {
        $term = new static($id);

        $data['seo_description'] = $data['description'];
        $dataMapper = new DataMapper($data);
        $term->term = $dataMapper->getProperty('term');
        $term->termLong = $dataMapper->getProperty('term_long');
        $term->useCaseCategoryIds = $dataMapper->getArrayProperty('use_case_categories', []);
        parent::appendData($term, $dataMapper);

        return $term;
    }

    public function getUrlPath(): string
    {
        return self::URL_PATH_PREFIX . '/' . $this->urlKey;
    }

    public function getSeoTitle(): string
    {
        return $this->metaTitle . self::TITLE_SUFFIX;
    }

    public function getUseCaseCategories(): array
    {
        return $this->useCaseCategoryIds;
    }
}
