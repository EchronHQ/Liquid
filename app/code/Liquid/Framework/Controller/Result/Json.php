<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Liquid\Framework\App\Response\ResponseInterface;
use Liquid\Framework\Controller\Result;

class Json extends Result
{
    private string $json = '';

    public function __construct(
        private \Liquid\Framework\Serialize\Serializer\Json $serializer
    )
    {

    }

    public function setData(string|array $data): self
    {
        $this->json = $this->serializer->serialize($data);
        return $this;
    }

    protected function render(ResponseInterface $response): Result
    {
        $response->setHeader('Content-Type', 'application/json', true);
        $response->setContent($this->json);
        return $this;
    }
}
