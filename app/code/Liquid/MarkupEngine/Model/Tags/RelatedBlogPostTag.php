<?php
declare(strict_types=1);

namespace Liquid\MarkupEngine\Model\Tags;

use Liquid\Blog\Model\ViewModel\RelatedBlogPostsSection;
use Liquid\Content\ViewModel\BaseViewModel;
use Liquid\Framework\DataObject;
use Liquid\Framework\ObjectManager\ObjectManagerInterface;
use Liquid\Framework\View\Element\BlockInterface;
use Liquid\Framework\View\Element\Template;
use Liquid\Framework\View\Layout\Layout;

class RelatedBlogPostTag extends DataObject implements BlockInterface
{
    public function __construct(
        private readonly Layout                 $layout,
        private readonly ObjectManagerInterface $objectManager,
        array                                   $data = []
    )
    {
        parent::__construct($data);
    }

    public function toHtml(): string
    {
        $relatedBlogPosts = $this->layout->createBlock(Template::class, '', ['data' => ['template' => 'Liquid_Blog::related-posts-section.phtml']]);

        $relatedBlogPostsViewModel = $this->objectManager->create(RelatedBlogPostsSection::class);
        $relatedBlogPostsViewModel->setTitle($this->getTitle());

        $relatedBlogPosts->setViewModel($relatedBlogPostsViewModel);
        $relatedBlogPosts->setViewModel($this->objectManager->get(BaseViewModel::class), 'base');

        return $relatedBlogPosts->toHtml();
    }

    private function getTitle(): string
    {
        if ($this->hasData('title')) {
            return $this->getData('title');
        }
        return 'More from us';
    }

}
