<?php
declare(strict_types=1);

namespace Liquid\Content\Block\Element\MarkupEngine;


use DI\Container;
use Liquid\Content\Block\Element\TabsControl;
use Liquid\Content\Helper\MarkupEngine;
use Liquid\Content\Model\MarkupEngine\BlockTag;
use Liquid\Core\Model\BlockContext;
use Liquid\Core\Model\Layout\Block;

class TabsTag extends Block
{

    public function __construct(BlockContext $context, private Container $container)
    {
        parent::__construct($context);
    }

    private function isActive(BlockTag $subTag): bool
    {
        if (!array_key_exists('active', $subTag->attributes)) {
            return true;
        }
        return $subTag->attributes['active'] === true || $subTag->attributes['active'] === 'true';
    }

    public function toHtml(): string
    {
        $content = $this->getData('content');

        $x = new MarkupEngine($this->container, $this->logger);
        $x->registerTag('tab', Block::class);
        // $x->debug = true;
        /** @var BlockTag[] $subTags */
        $subTags = $x->processTags('<div>' . $content . '</div>');
        // TODO: why is it needed to reverse the array to get the right order again?
        $subTags = array_reverse($subTags);
        $tabsControl = $this->getLayout()->createBlock(TabsControl::class);
        $tabsControl->tabs = [];
        foreach ($subTags as $subTag) {

            if ($subTag instanceof BlockTag && $subTag->tag === 'tab' && $this->isActive($subTag)) {
                $tabsControl->tabs[] = [
                    'title' => $subTag->attributes['title'],
                    'intro' => $subTag->attributes['intro'],
                    'content' => $subTag->contentHtml,
                ];
            }

        }


        return $tabsControl->toHtml();
    }
}
