<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\AbstractResult;

class Plain extends AbstractResult
{
    private string $text = '';

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    protected function render(HttpResponseInterface $response): self
    {
        $response->setHeader('Content-Type', 'text/plain', true);
        $response->setContent($this->text);
        return $this;
    }
}
