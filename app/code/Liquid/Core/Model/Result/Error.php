<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Result;

use Liquid\Core\Model\Request\Response;
use Laminas\Http\Response as ResponseAlias;

class Error extends Result
{


    public function __construct(protected readonly string $message = '', protected readonly int $statusCode = ResponseAlias::STATUS_CODE_404)
    {

    }

    protected function _render(Response $response): self
    {
        $response->setHttpResponseCode($this->statusCode);
        $response->setContent($this->message);

        return $this;
    }


}
