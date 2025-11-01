<?php

declare(strict_types=1);

namespace Liquid\Content\Block\Element;

use Liquid\Core\Model\Layout\Block;

class SectionBlock extends Block
{
    private string|null $background = null;

    public function toHtml(): string
    {
        $output = '<section ' . ($this->background !== null ? 'class="background-' . $this->background . '"' : '') . '>';
        $output .= '    <div class="container">';
        $output .= '        <div class="wrapper">';
        $output .= parent::toHtml();
        $output .= '        </div>';
        $output .= '    </div>';
        $output .= '</section>';
        return $output;
    }

    public function setBackground(string $background): void
    {
        $availableBackgrounds = ['ocean', 'medium', 'desert'];
        if (!\in_array($background, $availableBackgrounds)) {
            $this->logger->warning('[Section Block] Unknown background `' . $background . '`');
            return;
        }
        $this->background = $background;
    }
}
