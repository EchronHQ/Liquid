<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\AbstractResult;

class Xml extends AbstractResult
{
    private string $xml = '';

    protected function render(HttpResponseInterface $response): self
    {
        $response->setHeader('Content-Type', 'application/xml', true);
        $response->setBody($this->xml);
        return $this;
    }

    public function setData(string|array $data): self
    {
        $this->xml = $data;
        return $this;
    }
}
