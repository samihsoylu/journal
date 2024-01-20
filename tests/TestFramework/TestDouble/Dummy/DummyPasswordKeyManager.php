<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

use Defuse\Crypto\Key;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;

final class DummyPasswordKeyManager implements PasswordKeyManagerInterface
{
    public function createProtectedKeyForDb(string $password): string
    {
        return '';
    }

    public function encodeKeyForSession(string $protectedKey, string $password): string
    {
        return '';
    }

    public function unlockProtectedKey(string $protectedKey, string $password): Key
    {
        return Key::createNewRandomKey();
    }

    public function decodeSessionKey(string $encodedKey): Key
    {
        return Key::createNewRandomKey();
    }

    public function encryptData(string $plainText, Key $key): string
    {
        return '';
    }

    public function decryptData(string $cipherText, Key $key): string
    {
        return '';
    }

    public function updateProtectedKeyPassword(string $protectedKey, $oldPassword, $newPassword): string
    {
        return '';
    }
}
