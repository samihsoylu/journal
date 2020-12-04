<?php

namespace App\Utility;

/**
 * Stores instances in a static variable. This allows feeding mock instances in to service classes when PHPUnit testing
 */
class Registry
{
    protected static array $objects = [];

    public static function get(string $className): object
    {
        if (!isset(self::$objects[$className])) {
            self::$objects[$className] = new $className;
        }

        return self::$objects[$className];
    }

    public static function set(string $className, object $classObject): void
    {
        self::$objects[$className] = $classObject;
    }
}