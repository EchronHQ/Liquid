<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\Result;

class Xml extends Result
{
    private string $xml = '';

    protected function render(HttpResponseInterface $response): self
    {
        $response->setHeader('Content-Type', 'application/xml', true);
        $response->setContent($this->xml);
        return $this;
    }

    public function setData(string|array $data): self
    {
        $this->xml = $data;
        return $this;
    }
}
