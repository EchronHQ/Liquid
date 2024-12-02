<?php

declare(strict_types=1);

namespace Liquid\Blog\Controller\Term;

use Attlaz\Connector\Model\PlatformDefinition;
use Attlaz\Connector\Repository\PlatformRepository;
use Attlaz\Connector\Repository\UseCaseRepository;
use Liquid\Blog\Model\TermDefinition;
use Liquid\Blog\Model\ViewModel\Term;
use Liquid\Blog\Repository\TerminologyRepository;
use Liquid\Content\Helper\PageConfigHelper;
use Liquid\Content\Model\FrontendAction;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\ViewModel\BaseViewModel;
use Liquid\Framework\App\Action\Context;
use Liquid\Framework\App\Route\Attribute\Route;
use Liquid\Framework\Controller\Result;
use Liquid\Framework\Exception\NotFoundException;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\Result\Page;

#[Route('blog/term/view/term-id/:term-id', name: 'blog-term-view')]
class View extends FrontendAction
{
    /**
     * blog/post/view/post/why-data-connectivity
     * blog/why-data-connectivity
     */


    public function __construct(
        Context                                 $context,
        Layout                                  $layout,
        PageConfig                              $pageConfig,
        private readonly TerminologyRepository  $terminologyRepository,
        private readonly PlatformRepository     $platformRepository,
        private readonly UseCaseRepository      $useCaseRepository,
        private readonly ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($context, $layout, $pageConfig);
    }


    public function execute(): Result
    {
        $articlePage = $this->getArticleByRequest();
        if ($articlePage === null) {
            $this->logger->error('Unable to show term page, term not found', ['request' => $this->getRequest()->getParams()]);
            throw new NotFoundException('Page not found');

        }
        return $this->renderPage($articlePage);
    }


    private function getArticleByRequest(): TermDefinition|null
    {
        $termIdentifier = $this->getRequest()->getParam('term-id');
        if ($termIdentifier === null) {
            return null;
        }
        return $this->terminologyRepository->getById($termIdentifier);
    }


    private function renderPage(TermDefinition $term): Result
    {

        $result = $this->getResultFactory()->create(Page::class);

        $this->layout->runHandle('layout-1col');
        //        $this->layout->runHandle('layout-2col-left');


        //        $this->pageConfig->addBodyClass('header-dark');
        $this->pageConfig->addBodyClass('resource-term');
        $this->pageConfig->addBodyClass('resource-term-' . $term->id);
        $this->pageConfig->addBodyClass('theme-aqua');

        PageConfigHelper::append($term, $this->pageConfig);

        $termPageViewModel = $this->objectManager->create(Term::class);
        $termPageViewModel->setTerm($term);

        /**
         * Content
         */
        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
        $pageBlock->setTemplate('Liquid_Blog::term/view.phtml');


        $pageBlock->setViewModel($termPageViewModel);
        $pageBlock->setViewModel($this->objectManager->get(BaseViewModel::class), 'base');

        /**
         * Content
         */
        $contentBlock = $this->layout->createBlock(Template::class, 'term-content');
        $contentBlock->setTemplate($term->template);

        $contentBlock->setViewModel($termPageViewModel);
        $contentBlock->setViewModel($this->objectManager->get(BaseViewModel::class), 'base');

        $pageBlock->setChild('content', $contentBlock);

        /**
         * Use cases
         */

        $useCaseCategories = $term->getUseCaseCategories();

        $useCases = $this->useCaseRepository->getUseCasesByTypes($useCaseCategories);


        $useCasesBlock = $this->layout->createBlock(Template::class, 'term-use-cases');
        $useCasesBlock->setTemplate('Liquid_Blog::term/use_cases.phtml');


        $useCasesBlock->setData('use_cases', $useCases);

        // TODO: this is not correct
        $platform = new PlatformDefinition('');
        $platform->metaTitle = 'CRM';

        $useCasesBlock->setData('platform', $platform);

        $pageBlock->setChild('use_cases', $useCasesBlock);


//        $section = $this->layout->addBlock(SectionBlock::class, 'terms-list-wrapper', 'content');
//        $section->setBackground('medium');
//
//
//        $listBlock = $this->layout->addBlock(TagBar::class, 'terms-list', 'terms-list-wrapper');
//        $listBlock->setTags($this->terminologyRepository->getAll());
//        $listBlock->setCurrent($term);
//        $listBlock->setLabel('More Terms');

        //        /**
        //         * Explore more
        //         */
        //        $exploreBlock = $this->layout->addBlock(Post::class, 'explore-more', 'content');
        //        $exploreBlock->setTemplate('Liquid_Blog::explore-more.phtml');
        //        $exploreBlock->setData('post', $articlePage);

        /**
         * Demo
         */
//        $callToAction = $this->layout->addBlock(DemoCallToActionBlock::class, 'call-to-action', 'content');
//        assert($callToAction instanceof DemoCallToActionBlock);
        //        $callToAction->setTitle("Let's talk about what Attlaz can do for your business.");
        //    $callToAction->setDescription('A modern software architecture for all your business needs.');


        return $result;
    }


}
