<?php

declare(strict_types=1);

namespace Liquid\Blog\Model;

use Liquid\Content\Model\Resource\AbstractViewableEntity;
use Liquid\Core\Helper\DataMapper;

class TermDefinition extends AbstractViewableEntity
{
    public string $term;
    public string $termLong;
    protected string $controllerEndpoint = 'blog/term/view/term-id/';
    private array $useCaseCategoryIds = [];

    public static function generate(int|string $id, array $data): static
    {
        $term = new static($id);

        $data['seo_description'] = $data['description'];
        $dataMapper = new DataMapper($data);
        $term->term = $dataMapper->getProperty('term');
        $term->termLong = $dataMapper->getProperty('term_long');
        $term->useCaseCategoryIds = $dataMapper->getArrayProperty('use_case_categories', []);
        parent::appendData($term, $dataMapper);
        $term->urlRewrites[] = 'blog/term/' . $term->urlKey;
        return $term;
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
