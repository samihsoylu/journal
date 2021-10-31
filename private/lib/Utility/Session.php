<?php declare(strict_types=1);

namespace App\Utility;

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
        return isset($_SESSION[$name]);
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
     * @param string $name
     * @return null|mixed
     */
    public static function get(string $name)
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
        // Destroy all associated sessions
        session_destroy();

        // Start it again. In some cases we want to display an alert message to the user, we pass between redirects
        // these messages using sessions.
        session_start();
    }
}
