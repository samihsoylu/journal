<?php

declare(strict_types=1);

namespace App\Utility;

final class PrivateCookie
{
    public static function write(string $name, string $value, int $expires) : void
    {
        setcookie($name, $value, ['expires' => $expires, 'path' => '/', 'secure' => SSL_IS_ENABLED, 'httponly' => true, 'samesite' => 'Strict']);
    }
}
