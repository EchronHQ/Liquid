<?php
declare(strict_types=1);

namespace Liquid\Framework\Serialize\Serializer;

class Serialize implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serialize(string|int|float|bool|array|null $data): string|null
    {
        return \serialize($data);
    }

    /**
     * @inheritDoc
     */
    public function unserialize(string $string): string|int|float|bool|array|null
    {
        if ('' === $string) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
        \set_error_handler(
            static function () {
                \restore_error_handler();
                throw new \InvalidArgumentException('Unable to unserialize value, string is corrupted.');
            }
        );
        $result = \unserialize($string, ['allowed_classes' => false]);
        \restore_error_handler();
        return $result;
    }
}
