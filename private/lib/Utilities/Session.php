<?php

namespace App\Utilities;

class Session
{
    /**
     * Checks if a Session exists
     *
     * @param string $name
     * @return bool
     */
    public static function exists(string $name): bool
    {
        return (isset($_SESSION[$name])) ? true : false;
    }

    /**
     * Creates a Session
     *
     * @param string $name
     * @param mixed $value
     */
    public static function put(string $name, $value): void
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Reads a Session
     *
     * @param $name
     * @return null|mixed
     */
    public static function get($name)
    {
        return $_SESSION[$name] ?? null;
    }

    /**
     * Removes a Session
     *
     * @param string $name
     */
    public static function delete(string $name): void
    {
        if (self::exists($name)) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * Destroys all Session associated with the user
     *
     * @return void
     */
    public static function destroy(): void
    {
        session_destroy();
    }
}
