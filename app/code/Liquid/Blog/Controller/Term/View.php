<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Term;

use Attlaz\Connector\Model\PlatformDefinition;
use Attlaz\Connector\Repository\PlatformRepository;
use Attlaz\Connector\Repository\UseCaseRepository;
use Liquid\Blog\Block\Term;
use Liquid\Blog\Model\TermDefinition;
use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Content\Block\Element\DemoCallToActionBlock;
use Liquid\Content\Block\Element\SectionBlock;
use Liquid\Content\Block\Element\TagBar;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Core\Layout;
use Liquid\Core\Model\Action\Context;
use Liquid\Core\Model\Result\Page;
use Liquid\Core\Model\Result\Result;

class View extends FrontendAction
{
    /**
     * blog/post/view/post/why-data-connectivity
     * blog/why-data-connectivity
     */


    public function __construct(
        Context                                $context,
        Layout                                 $layout,
        PageConfig                             $pageConfig,
        private readonly TerminologyRepository $terminologyRepository,
        private readonly PlatformRepository    $platformRepository,
        private readonly UseCaseRepository     $useCaseRepository,
    )
    {
        parent::__construct($context, $layout, $pageConfig);
    }


    public function execute(): Result|null
    {
        $articlePage = $this->getArticleByRequest();
        if ($articlePage === null) {
            $this->logger->error('Unable to show term page, term not found', ['request' => $this->getRequest()->getParams()]);
            return null;

        }
        return $this->renderPage($articlePage);
    }


    private function getArticleByRequest(): TermDefinition|null
    {
        $termIdentifier = $this->getRequest()->getParam('termId');
        if ($termIdentifier === null) {
            return null;
        }
        return $this->terminologyRepository->getByUrlKey($termIdentifier);
    }


    private function renderPage(TermDefinition $term): Result
    {


        $this->layout->runHandle('layout-1col');
        //        $this->layout->runHandle('layout-2col-left');


        //        $this->pageConfig->addBodyClass('header-dark');
        $this->pageConfig->addBodyClass('resource-term');
        $this->pageConfig->addBodyClass('resource-term-' . $term->id);
        $this->pageConfig->addBodyClass('theme-aqua');

        PageConfigHelper::append($term, $this->pageConfig);

        /**
         * Content
         */
        $pageBlock = $this->layout->addBlock(Term::class, 'page', 'content');
        $pageBlock->setTemplate('Liquid_Blog::term/view.phtml');
        $pageBlock->setData('term', $term);

        /**
         * Content
         */
        $contentBlock = $this->layout->createBlock(Term::class, 'term-content');
        $contentBlock->setTemplate($term->template);
        $contentBlock->setData('term', $term);

        $pageBlock->setChild('content', $contentBlock);

        /**
         * Use cases
         */

        $useCaseCategories = $term->getUseCaseCategories();

        $useCases = $this->useCaseRepository->getUseCasesByTypes($useCaseCategories);


        $useCasesBlock = $this->layout->createBlock(Term::class, 'term-use-cases');
        $useCasesBlock->setTemplate('Liquid_Blog::term/use_cases.phtml');
        $useCasesBlock->setData('use_cases', $useCases);

        // TODO: this is not correct
        $platform = new PlatformDefinition('');
        $platform->metaTitle = 'CRM';

        $useCasesBlock->setData('platform', $platform);

        $pageBlock->setChild('use_cases', $useCasesBlock);


        $section = $this->layout->addBlock(SectionBlock::class, 'terms-list-wrapper', 'content');
        $section->setBackground('medium');


        $listBlock = $this->layout->addBlock(TagBar::class, 'terms-list', 'terms-list-wrapper');
        $listBlock->setTags($this->terminologyRepository->getAll());
        $listBlock->setCurrent($term);
        $listBlock->setLabel('More Terms');

        //        /**
        //         * Explore more
        //         */
        //        $exploreBlock = $this->layout->addBlock(Post::class, 'explore-more', 'content');
        //        $exploreBlock->setTemplate('Liquid_Blog::explore-more.phtml');
        //        $exploreBlock->setData('post', $articlePage);

        /**
         * Demo
         */
        $callToAction = $this->layout->addBlock(DemoCallToActionBlock::class, 'call-to-action', 'content');
        assert($callToAction instanceof DemoCallToActionBlock);
        //        $callToAction->setTitle("Let's talk about what Attlaz can do for your business.");
        //    $callToAction->setDescription('A modern software architecture for all your business needs.');


        return $this->getResultFactory()->create(Page::class);
    }


}
