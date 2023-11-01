<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Result;

use Liquid\Core\Model\Request\Response;

class Json extends Result
{
    private string $json = '';

    protected function _render(Response $response): self
    {
        $response->setHeader('Content-Type', 'application/json', true);
        $response->setContent($this->json);
        return $this;
    }

    public function setData(string|array $data): self
    {
        $this->json = \json_encode($data);
        return $this;
    }
}
