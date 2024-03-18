<?php

declare(strict_types=1);

use DI\Container;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManager;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\CipherSuite\TransientAesEncryptor\TransientAesEncryptor;
use SamihSoylu\CipherSuite\TransientAesEncryptor\TransientAesEncryptorInterface;

return function (Container $container): void {
    $container->set(PasswordKeyManagerInterface::class, fn (Container $container): PasswordKeyManager => $container->get(PasswordKeyManager::class));

    $container->set(TransientAesEncryptorInterface::class, fn (Container $container): TransientAesEncryptor => $container->get(TransientAesEncryptor::class));
};
