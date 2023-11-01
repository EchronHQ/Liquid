<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Result;

use Liquid\Core\Model\Request\Response;

class Xml extends Result
{
    private string $xml = '';

    protected function _render(Response $response): self
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
