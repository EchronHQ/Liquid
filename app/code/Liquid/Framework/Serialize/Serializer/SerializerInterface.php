<?php
declare(strict_types=1);

namespace Liquid\Framework\Serialize\Serializer;

interface SerializerInterface
{
    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     * @return string|null
     * @throws \InvalidArgumentException
     */
    public function serialize(string|int|float|bool|array|null $data): string|null;

    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     */
    public function unserialize(string $string): string|int|float|bool|array|null;
}
