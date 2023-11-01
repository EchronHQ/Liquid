<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Result;

use Liquid\Core\Model\Request\Response;

class Plain extends Result
{
    private string $text = '';

    protected function _render(Response $response): self
    {
        $response->setHeader('Content-Type', 'text/plain', true);
        $response->setContent($this->text);
        return $this;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }
}
