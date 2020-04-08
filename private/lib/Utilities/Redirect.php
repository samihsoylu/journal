<?php

namespace App\Utilities;

class Redirect
{
    /**
     * Redirects the user to a different url
     *
     * @param string $location
     */
    public static function to(string $location): void
    {
        header("Location: {$location}");
        exit();
    }
}