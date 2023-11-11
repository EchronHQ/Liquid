<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use DI\Attribute\Inject;
use Liquid\Core\Helper\Resolver;
use Liquid\Core\Model\FrontendFileUrl;
use Liquid\Core\Model\Layout\AbstractBlock;
use Psr\Log\LoggerInterface;

class CopyBlock extends AbstractBlock
{
    private string|null $types = null;

    private FrontendFileUrl|null $headerIcon = null;
    private string|null $headerIconStyle = null;

    private string|null $headerTitle = null;
    private string|null $headerTitleTag = null;

    private string|null $headerTitleId = null;

    private string|null $headerCaption = null;

    private string|null $content = null;

    private string|null $footer = null;


    public function __construct(
        string|null                                                             $types = null,
        #[Inject(Resolver::class)] private readonly Resolver|null               $resolver = null,
        #[Inject(LoggerInterface::class)] private readonly LoggerInterface|null $logger = null,
        //        #[Inject] private Profiler|null $profiler = null
    )
    {
        $this->validateTypes($types);
        $this->types = $types;
    }

    private function validateTypes(string|null $types): void
    {
        $allowedTypes = [
            'detail', 'center', 'right', 'gap-normal', 'gap-large',
            'card', 'card-headerimage', 'card--paddingMedium', 'card--shadowNormal', 'card--theme-aqua',
            'green', 'blue', 'yellow', 'purple', 'pink', 'new', 'header',
            'col--3', 'col--4', 'col--6', 'col--12',
            'sm:col--3', 'sm:col--4', 'sm:col--6', 'sm:col--12',
            'md:col--3', 'md:col--4', 'md:col--6', 'md:col--12',
            'lg:col--3', 'lg:col--4', 'lg:col--6', 'lg:col--12',

        ];


        if (!empty($types)) {
            $arrTypes = \explode(' ', $types);
            foreach ($arrTypes as $type) {
                if ($type !== '' && !\in_array($type, $allowedTypes)) {
                    // TODO: log this
                    throw new \Exception('Invalid content block type "' . $type . '"');
                }
            }
        }
    }

    public function setHeaderIcon(string|FrontendFileUrl $icon, string|null $style = null): void
    {
        if (!$icon instanceof FrontendFileUrl && !str_starts_with($icon, 'http')) {
            if (str_starts_with($icon, 'http')) {
                $icon = new FrontendFileUrl($icon);
            } else if ($this->resolver !== null) {
                $icon = $this->resolver->getAssetUrl($icon);
            } else {
                if ($this->logger !== null) {
                    $this->logger->error('Header icon not found', ['icon' => $icon, 'style' => $style]);
                }
                //  $icon = new FrontendFileUrl($icon);
                return;
            }

        }

        $this->headerIcon = $icon;

        $this->headerIconStyle = $style;
    }

    public function setHeaderTitle(string $headerTitle, string $tag = 'h2', string|null $id = null): void
    {
        $this->headerTitle = $headerTitle;
        $this->headerTitleTag = $tag;
        $this->headerTitleId = $id;
    }

    public function setHeaderCaption(string $headerCaption): void
    {
        $this->headerCaption = $headerCaption;
    }

    public function setContent(string|null $content): void
    {
        $this->content = $content;
    }

    /**
     * Add content to the end of the current content
     * @param string $content
     * @param bool $prefixWithSpace
     * @return void
     */
    public function appendContent(string $content, bool $prefixWithSpace = true): void
    {
        if ($this->content === null) {
            $this->content = '';
            $prefixWithSpace = false;
        }
        $this->content .= ($prefixWithSpace ? ' ' : '') . $content;
    }

    public function addContentLine(string $contentLine, string $tag = 'div'): void
    {
        $this->content .= '<' . $tag . '>' . $contentLine . '</' . $tag . '>';
    }

    public function addContentList(array $listLines): void
    {
        $serializedListLines = '';
        foreach ($listLines as $listLine) {
            $serializedListLines .= '<li>' . $listLine . '</li>';
        }
        $this->content .= '<ul class="list-marker">' . $serializedListLines . '</ul>';
    }

    public function setFooter(string $footer): void
    {
        $this->footer = $footer;
    }

    public function toHtml(): string
    {
        //        if ($this->profiler !== null) {
        //            $this->profiler->profilerStart('toHtml ' . $this->getNameInLayout());
        //        }
        $x = $this->types !== null ? ' ' . $this->types : '';


        $output = '<div class="copy' . $x . '">';
        if ($this->headerTitle !== null) {


            $output .= '    <div class="copy-header">';

            if ($this->headerIcon !== null) {
                $output .= '<div class="copy-icon ' . (\is_null($this->headerIconStyle) ? '' : $this->headerIconStyle) . '">';
                $output .= '    <img loading="lazy" src="' . $this->headerIcon->url . '" alt="' . $this->headerTitle . '">';
                $output .= '</div>';
            }
            if ($this->headerCaption !== null) {
                $output .= '<div class="copy-caption">' . $this->headerCaption . '</div>';
            }


            //            $output .= '        <div class="copy-title">';

            $output .= '            <' . $this->headerTitleTag . ' class="copy-title" ' . ($this->headerTitleId !== null ? 'id="' . $this->headerTitleId . '"' : '') . ' >' . $this->headerTitle . '</' . $this->headerTitleTag . '>';
            //            $output .= '        </div>';
            $output .= '    </div>';
        }

        if ($this->content !== null) {
            $output .= '    <div class="copy-content">';
            $output .= '        ' . $this->content;
            $output .= '    </div>';
        }

        if ($this->footer !== null) {
            $output .= '    <div class="copy-footer">';
            $output .= '        <div>' . $this->footer . '</div>';
            $output .= '    </div>';

        }

        $output .= '</div>';

        //        if ($this->profiler !== null) {
        //            $this->profiler->profilerFinish('toHtml ' . $this->getNameInLayout());
        //        }
        return $output;
    }

    public function __toString(): string
    {
        // TODO: this should not be used but is implemented in case someone makes a mistake, shall we log this?
        return $this->toHtml();
    }

//    public function setAttributes($input): void
//    {
//
//    }
//
//    public function getTag(): string
//    {
//        return 'div';
//    }
//
//    public function getAttributes(): array
//    {
//        return ['class' => 'copy'];
//    }
//
//    public function toHtml(\DOMDocument $document, \DOMElement $element): \DOMElement
//    {
//        $attributes = $element->attributes;
//
//
//        $link = $document->createElement('div');
////        $elementAttributes = $instance->getAttributes();
////        foreach ($elementAttributes as $elementAttribute => $value) {
////            $link->setAttribute($elementAttribute, $value);
////        }
//
//
//        $children = $element->childNodes;
//
//        foreach ($children as $child) {
//
//            //Replace copy header
//            //Replace copy content
//            $link->appendChild($child);
//        }
//    }


}
