<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManager;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\CipherSuite\TransientAesEncryptor\TransientAesEncryptor;
use SamihSoylu\CipherSuite\TransientAesEncryptor\TransientAesEncryptorInterface;
use SamihSoylu\Journal\Infrastructure\Adapter\Action\Synchronous\SynchronousActionDispatcher;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionDispatcherInterface;

return function (Container $container) {
    $container->set(PasswordKeyManagerInterface::class, function (Container $container) {
        return $container->get(PasswordKeyManager::class);
    });

    $container->set(TransientAesEncryptorInterface::class, function (Container $container) {
       return $container->get(TransientAesEncryptor::class);
    });
};