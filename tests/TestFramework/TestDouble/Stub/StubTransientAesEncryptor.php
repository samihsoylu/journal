<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub;

use SamihSoylu\CipherSuite\TransientAesEncryptor\TransientAesEncryptorInterface;

final class StubTransientAesEncryptor implements TransientAesEncryptorInterface
{
    public function encrypt(string $plaintext): string
    {
        return 'encrypted:' . $plaintext;
    }

    public function decrypt(string $encryptedString): string
    {
        return str_replace('encrypted:', '', $encryptedString);
    }
}