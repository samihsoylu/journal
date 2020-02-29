<?php

namespace App\Twig;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class TwigProvider
{
    /**
     * @var Environment|null
     */
    private static $twigInstance = null;

    private function __construct()
    {
        $loader = new FilesystemLoader([TEMPLATE_PATH]);
        $twigInstance = new Environment($loader, [
            'cache' => BASE_PATH . '/private/cache/',
        ]);

        self::$twigInstance = $twigInstance;
    }

    public static function getInstance(): Environment
    {
        if (self::$twigInstance === null) {
            new self();
        }

        return self::$twigInstance;
    }
}