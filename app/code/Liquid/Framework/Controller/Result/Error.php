<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;

use Laminas\Http\Response as HttpResponse;
use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\AbstractResult;

class Error extends AbstractResult
{


    public function __construct(
        protected readonly string $message = '',
        protected readonly int    $statusCode = HttpResponse::STATUS_CODE_404
    )
    {

    }

    protected function render(HttpResponseInterface $response): self
    {
        $response->setHttpResponseCode($this->statusCode);
        $response->setContent($this->message);

        return $this;
    }
}
