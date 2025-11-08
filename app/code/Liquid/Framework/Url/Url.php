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

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function getFragments(): string
    {
        return $this->fragments;
    }
}
