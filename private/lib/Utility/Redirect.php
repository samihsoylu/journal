<?php

declare(strict_types=1);

namespace App\Utility;

final class Redirect
{
    /**
     * Redirects the user to a different url.
     */
    public static function to(string $location) : never
    {
        header('Location: ' . $location);
        exit;
    }
}
