<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Infrastructure\Adapter\PasswordHasher\Argon2IdPasswordHasher;
use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;

return function (Container $container) {
    $container->set(PasswordHasherInterface::class, function (Container $container) {
        return $container->get(Argon2IdPasswordHasher::class);
    });
};