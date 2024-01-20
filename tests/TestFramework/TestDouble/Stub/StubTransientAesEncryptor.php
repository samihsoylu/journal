<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub;

use SamihSoylu\CipherSuite\TransientAesEncryptor\TransientAesEncryptorInterface;

final readonly class StubTransientAesEncryptor implements TransientAesEncryptorInterface
{
    private const PREFIX = 'fake-encryption::';

    public function encrypt(string $plaintext): string
    {
        return self::PREFIX . $plaintext;
    }

    public function decrypt(string $encryptedString): string
    {
        return str_replace(self::PREFIX, '', $encryptedString);
    }
}
