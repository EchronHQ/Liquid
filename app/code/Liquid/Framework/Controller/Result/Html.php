<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Liquid\Framework\App\Response\Response;

class Html extends Result
{
    private string $html = '';

    protected function _render(Response $response): self
    {
        // TODO: is this ever used?
        //        die('render html');
        $response->setHeader('Content-Type', 'text/html', true);
        $response->setHeader('Content-Security-Policy', "default-src 'self'");
        $response->setContent($this->html);
        return $this;
    }

    public function setHtml(string $html): self
    {
        $this->html = $html;
        return $this;
    }
}
