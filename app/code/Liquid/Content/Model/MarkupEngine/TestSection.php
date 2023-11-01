<?php

declare(strict_types=1);

namespace Liquid\Content\Model\MarkupEngine;

class TestSection extends CustomTag
{
    public function render(): string
    {
        $block = $this->type ?? "container";
        return <<< HTML
			<div class="{$block}" style="background-color: red">
				{$this->contentHtml}
			</div>
HTML;
    }
}
