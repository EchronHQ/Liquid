<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Liquid\Framework\App\Response\HttpResponseCode;
use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\AbstractResult;

class Error extends AbstractResult
{


    public function __construct(
        protected readonly string           $message = '',
        protected readonly HttpResponseCode $statusCode = HttpResponseCode::NOT_FOUND
    )
    {

    }

    protected function render(HttpResponseInterface $response): self
    {
        $response->setHttpResponseCode($this->statusCode);
        $response->setBody($this->message);

        return $this;
    }
}
