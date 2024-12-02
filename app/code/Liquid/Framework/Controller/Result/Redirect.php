<?php
declare(strict_types=1);

namespace Liquid\Framework\Controller\Result;


use Liquid\Framework\App\Response\HttpResponseInterface;
use Liquid\Framework\Controller\Result;

class Redirect extends Result
{
    private string $url;

    public function __construct(string $url, int|null $httpResponseCode = null)
    {
        $this->url = $url;
        if ($httpResponseCode !== null) {
            $this->httpResponseCode = $httpResponseCode;
        }
    }

    /**
     * URL Setter
     *
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    protected function render(HttpResponseInterface $response): self
    {
        if (empty($this->httpResponseCode)) {
            $response->setRedirect($this->url);
        } else {
            $response->setRedirect($this->url, $this->httpResponseCode);
        }
        return $this;
    }

}
