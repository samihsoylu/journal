<?php

declare(strict_types=1);

namespace App\Utility;

final class Redirect
{
    public static function localDestination(mixed $path, string $fallback) : string
    {
        if ( ! is_string($path) || ! str_starts_with($path, '/') || str_starts_with($path, '//')
            || str_contains($path, '\\') || preg_match('/[\x00-\x20\x7f]/', $path)) {
            return $fallback;
        }

        return $path;
    }

    /**
     * Redirects the user to a different url.
     */
    public static function to(string $location) : never
    {
        header('Location: ' . $location);
        exit;
    }
}
