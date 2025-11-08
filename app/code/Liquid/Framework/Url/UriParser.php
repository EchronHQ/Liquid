<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;

use Symfony\Component\HttpFoundation\Request;

class UriParser
{
    public function parse(string $input): Url
    {

        $request = Request::create($input);

        $url = new Url();
        $url->scheme = $request->getScheme();
        $url->host = $request->getHost();
        $url->port = $request->getPort();

        $url->user = $request->getUser();
        $url->password = $request->getPassword();

        $url->path = $request->getBasePath();
        $url->query = $request->query->all(null);

        $url->fragments = '';

        return $url;
    }
}
