<?php

declare(strict_types=1);

namespace Liquid\Blog\Block;

use Liquid\Blog\Model\TermDefinition;
use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Content\Block\TemplateBlock;
use Liquid\Content\Helper\TemplateHelper;
use Liquid\Core\Model\BlockContext;

class Term extends TemplateBlock
{
    public function __construct(
        BlockContext $context,
        TemplateHelper $templateHelper,
        protected TerminologyRepository $terminologyRepository
    ) {
        parent::__construct($context, $templateHelper);
    }

    public function getTerm(): TermDefinition
    {
        return $this->getData('term');
    }

    /**
     * @return TermDefinition[]
     */
    public function getTerms(): array
    {
        return $this->terminologyRepository->getAll();
    }
}
