<?php
declare(strict_types=1);

namespace Liquid\Framework\Serialize\Serializer;

class Json implements SerializerInterface
{
    /**
     * @inheritdoc
     * @throws \JsonException
     */
    public function serialize(float|int|bool|array|string|null $data): string|null
    {
        $result = \json_encode($data, JSON_THROW_ON_ERROR);
        if (false === $result) {
            throw new \InvalidArgumentException("Unable to serialize value. Error: " . \json_last_error_msg());
        }
        return $result;
    }

    /**
     * @inheritdoc
     * @throws \JsonException
     */
    public function unserialize(string $string): string|int|float|bool|array|null
    {
        $result = \json_decode($string, true, 512, JSON_THROW_ON_ERROR);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Unable to unserialize value. Error: " . \json_last_error_msg());
        }
        return $result;
    }
}
