<?php

declare(strict_types=1);

namespace Liquid\Blog\Model\ViewModel;

use Liquid\Blog\Model\TermDefinition;
use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Framework\View\Element\ArgumentInterface;

class Term implements ArgumentInterface
{
    private TermDefinition|null $term = null;

    public function __construct(protected TerminologyRepository $terminologyRepository)
    {

    }

    public function getTerm(): TermDefinition
    {
        return $this->term;
    }

    public function setTerm(TermDefinition $term): void
    {
        $this->term = $term;
    }

    /**
     * @return TermDefinition[]
     */
    public function getTerms(): array
    {
        return $this->terminologyRepository->getAll();
    }
}
