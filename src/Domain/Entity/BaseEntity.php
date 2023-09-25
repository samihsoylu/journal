<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Entity;

abstract class BaseEntity
{
    abstract public function checkErrors(): void;

    /**
     * @param string[] $requiredProperties
     */
    protected function assertRequiredPropertiesProvided(array $requiredProperties): void
    {
        $entityName = static::getEntityName();

        $properties = get_object_vars($this);
        foreach ($requiredProperties as $propertyName) {
            if (empty($properties[$propertyName])) {
                throw new \LogicException(
                    "{$entityName} validation failed, Property '{$propertyName}' is null or empty"
                );
            }
        }
    }

    public static function getEntityName(): string
    {
        return str_replace(__NAMESPACE__ . '\\', '', static::class);
    }
}