<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\AbstractResult;

class Html extends AbstractResult
{
    private string $html = '';

    public function setHtml(string $html): self
    {
        $this->html = $html;
        return $this;
    }

    protected function render(HttpResponseInterface $response): AbstractResult
    {
        $response->setHeader('Content-Type', 'text/html', true);
        $response->setHeader('Content-Security-Policy', "default-src 'self'");
        $response->setContent($this->html);
        return $this;
    }
}
