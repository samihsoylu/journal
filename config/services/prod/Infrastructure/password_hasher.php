<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\Journal\Framework\Infrastructure\Adapter\PasswordHasher\Argon2Id\Argon2IdPasswordHasher;
use SamihSoylu\Journal\Framework\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;

return function (Container $container): void {
    $container->set(PasswordHasherInterface::class, fn (Container $container) => $container->get(Argon2IdPasswordHasher::class));
};
