<?php
declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Content\Block\Html\Script;
use Liquid\Content\Model\Resource\PageDefinition;
use Liquid\Content\Model\View\Page\PageConfig;
use Liquid\Content\ViewModel\BaseViewModel;
use Liquid\Content\ViewModel\HtmlHead;
use Liquid\Framework\Controller\AbstractResult;
use Liquid\Framework\Controller\ResultFactory;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout;
use Liquid\Framework\View\Result\Page;

class PageHelper
{
    public function __construct(
        private readonly ResultFactory          $resultFactory,
        private readonly Layout                 $layout,
        private readonly PageConfig             $pageConfig,
        private readonly ObjectManagerInterface $objectManager,
    )
    {
    }

    public function prepareResultPage(PageDefinition $page): AbstractResult
    {
        $result = $this->resultFactory->create(Page::class);

        $this->layout->runHandle('layout-1col');

        /** @var Template $headBlock */
        $headBlock = $this->layout->getBlock('head');
        /** @var HtmlHead $headViewModel */
        $headViewModel = $headBlock->getViewModel('', HtmlHead::class);

        if ($page->id === 'contact') {
            $headViewModel->addScript(new Script('js/contact.js'));
        }
        if ($page->id === 'demo') {
            $headViewModel->addScript(new Script('js/demo.js'));
        }


        $this->pageConfig->addBodyClass('page-' . $page->id);

        PageConfigHelper::append($page, $this->pageConfig);

        $baseViewModel = $this->objectManager->get(BaseViewModel::class);
        $pageBlock = $this->layout->addBlock(Template::class, 'page', 'content');
        $pageBlock->setViewModel($baseViewModel)->setTemplate($page->template);
        $pageBlock->setData('page', $page);


        return $result;
    }
}
