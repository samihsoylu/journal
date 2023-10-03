<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Entity;

abstract class BaseEntity
{
    abstract public function checkErrors(): void;

    /**
     * @param array<string> $requiredProperties
     */
    protected function assertRequiredPropertiesProvided(array $requiredProperties): void
    {
        $entityName = static::getEntityName();

        $properties = get_object_vars($this);
        foreach ($requiredProperties as $propertyName) {
            if (empty($properties[$propertyName])) {
                throw new \LogicException(
                    "Failed to perform save operation: The entity '{$entityName}' is missing a required property. The property '{$propertyName}' must be set and cannot be null or empty."
                );
            }
        }
    }

    public static function getEntityName(): string
    {
        return str_replace(__NAMESPACE__ . '\\', '', static::class);
    }
}
