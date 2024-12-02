<?php
declare(strict_types=1);

namespace Liquid\Content\Block\Element\MarkupEngine;


use Liquid\Content\Block\Element\TabsControl;
use Liquid\Content\Helper\MarkupEngine;
use Liquid\Content\Model\MarkupEngine\BlockTag;
use Liquid\Core\Model\BlockContext;
use Liquid\Core\Model\Layout\Block;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\Template;

class TabsTag extends Block
{

    public function __construct(
        BlockContext                            $context,
        private readonly ObjectManagerInterface $objectManager
    )
    {
        parent::__construct($context);
    }

    public function toHtml(): string
    {
        $content = $this->getData('content');

        $x = new MarkupEngine($this->objectManager, $this->logger);
        $x->registerTag('tab', Block::class);
        /** @var BlockTag[] $subTags */
        $subTags = $x->processTags('<div>' . $content . '</div>');
        // TODO: why is it needed to reverse the array to get the right order again?
        $subTags = array_reverse($subTags);

        $viewModel = $this->objectManager->create(TabsControl::class);
        $viewModel->tabs = [];

        foreach ($subTags as $subTag) {

            if ($subTag instanceof BlockTag && $subTag->tag === 'tab' && $this->isActive($subTag)) {

                $title = '[No title]';
                if (isset($subTag->attributes['title'])) {
                    $title = $subTag->attributes['title'];
                } else {
                    // TODO: log this
                    $this->logger->warning('No title defined for tab', ['tab' => $subTag]);
                }
                $viewModel->tabs[] = [
                    'title' => $title,
                    'content' => $subTag->contentHtml,
                ];
            }

        }
        /** @var Template $tabsControl */
        $tabsControl = $this->getLayout()->createBlock(Template::class, '', ['data' => ['template' => 'Liquid_Content::element/tabscontrol.phtml']]);
        $tabsControl->setViewModel($viewModel);
        return $tabsControl->toHtml();
    }

    private function isActive(BlockTag $subTag): bool
    {
        if (!array_key_exists('active', $subTag->attributes)) {
            return true;
        }
        return $subTag->attributes['active'] === true || $subTag->attributes['active'] === 'true';
    }
}
