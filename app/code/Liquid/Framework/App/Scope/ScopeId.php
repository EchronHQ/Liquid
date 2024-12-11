<?php
declare(strict_types=1);

namespace Liquid\Framework\App\Scope;

abstract class ScopeId implements \Stringable
{
    public function __construct(private readonly string $id)
    {

    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function equals(ScopeId $id): bool
    {
        return $this->id === $id->id;
    }
}
