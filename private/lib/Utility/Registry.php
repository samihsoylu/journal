<?php declare(strict_types=1);

namespace App\Utility;

/**
 * Stores instances in a static variable. This allows registering mock instances in to service classes when performing
 * tests with phpunit.
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
