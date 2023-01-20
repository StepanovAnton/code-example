<?php

declare(strict_types=1);

namespace App\Packages\TriggerEvent\Traits;

use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;

trait AssignArrayTrait
{
    /**
     * @throws ReflectionException
     */
    public function assign(array $options): static
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $propertyType = (new ReflectionProperty($this::class, $key))->getType();

                if ($propertyType instanceof ReflectionNamedType) {
                    $propertyTypeName = $propertyType->getName();

                    $this->$key = match ($propertyTypeName) {
                        'array' => json_decode($value, true),
                        'int' => (int)$value,
                        'bool' => (bool)$value,
                        default => $value
                    };
                }
            }
        }

        return $this;
    }
}
