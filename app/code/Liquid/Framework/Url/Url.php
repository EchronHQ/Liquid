<?php
declare(strict_types=1);

namespace Liquid\Framework\Url;

class Url
{
    public string $scheme;
    public string $host;
    public int|null $port = null;

    public string|null $user;
    public string|null $password = null;

    public string $path;
    public array $query;
    public string $fragments;
}
