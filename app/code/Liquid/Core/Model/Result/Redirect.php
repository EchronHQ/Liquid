<?php

declare(strict_types=1);

namespace Liquid\Core\Model\Result;

use Liquid\Core\Model\Request\Response;

class Redirect extends Result
{
    public string $url;

    public function __construct(string $url, int|null $httpResponseCode = null)
    {
        $this->url = $url;
        if ($httpResponseCode !== null) {
            $this->httpResponseCode = $httpResponseCode;
        }
    }

    protected function _render(Response $response): self
    {
        if (empty($this->httpResponseCode)) {
            $response->setRedirect($this->url);
        } else {
            $response->setRedirect($this->url, $this->httpResponseCode);
        }
        return $this;
    }

}
