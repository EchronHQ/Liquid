<?php

declare(strict_types=1);

namespace Liquid\Core\Helper;

use Echron\Tools\Time;

class DataMapper
{
    private array $propertyHits = [];

    public function __construct(private readonly array $data)
    {

    }

    private function getPropertyValue(string $property): mixed
    {
        if (\array_key_exists($property, $this->data)) {
            $this->propertyHits[] = $property;
            return $this->data[$property];
        }
        return null;
    }

    public function getProperty(string $key, string|null $default = ''): string|null
    {
        $value = $this->getPropertyValue($key);
        if ($value !== null) {
            return $value;
        }
        return $default;
    }

    public function getUntypedProperty(string $property, mixed $default = null): mixed
    {
        $value = $this->getPropertyValue($property);
        if ($value !== null) {
            return $value;
        }
        return $default;
    }

    public function getDateTimeProperty(string $property, \DateTime|null $default = null): \DateTime|null
    {
        $value = $this->getPropertyValue($property);
        if ($value !== null) {
            if ($value instanceof \DateTime) {
                return $value;
            }

            return Time::createFromFormat($value);
        }
        return $default;
    }

    public function getArrayProperty(string $property, array|null $default = null): array|null
    {
        $value = $this->getPropertyValue($property);
        if (\is_array($value)) {
            return $value;
        }
        return $default;
    }

    public function getBooleanProperty(string $property, bool|null $default = null): bool|null
    {
        $value = $this->getPropertyValue($property);
        if ($value === true || $value === 1 || $value === 'true' || $value === '1') {
            return true;
        }
        if ($value === false || $value === 0 || $value === 'false' || $value === '0') {
            return false;
        }
        return $default;
    }

    public function getNotUsedProperties(): array
    {
        $dataProperties = \array_keys($this->data);
        $notUsed = \array_diff($dataProperties, $this->propertyHits);

        return \array_values($notUsed);
    }

    public function report(): void
    {
        //        $notUsedProperties = $this->getNotUsedProperties();
        //        foreach ($notUsedProperties as $notUsedProperty) {
        //
        //        }
    }
}
