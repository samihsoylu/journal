<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Application\Core\User\UseCase\Create\CreateUserActionHandler;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Infrastructure\Adapter\Cache\EncryptedTransient\EncryptedTransientCache;

return function (Container $container) {
    $container->set(CreateUserActionHandler::class, function (Container $container) {
        return new CreateUserActionHandler(
            $container->get(UserRepositoryInterface::class),
            $container->get(EncryptedTransientCache::class),
            $container->get(PasswordKeyManagerInterface::class),
        );
    });
};