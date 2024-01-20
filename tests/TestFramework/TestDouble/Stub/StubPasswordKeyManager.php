<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub;

use Defuse\Crypto\Key;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Utility\Assert;

final class StubPasswordKeyManager implements PasswordKeyManagerInterface
{
    public function __construct(
        private string $createProtectedKeyForDbWillReturn = '',
        private string $encodeKeyForSessionWillReturn = '',
        private ?Key $unlockProtectedKeyWillReturn = null,
        private ?Key $decodeSessionKeyWillReturn = null,
        private string $encyptDataWillReturn = '',
        private string $decryptDataWillReturn = '',
        private string $updateProtectedKeyPasswordWillReturn = '',
    ) {}

    public function createProtectedKeyForDb(string $password): string
    {
        return $this->createProtectedKeyForDbWillReturn;
    }

    public function encodeKeyForSession(string $protectedKey, string $password): string
    {
        return $this->encodeKeyForSessionWillReturn;
    }

    public function unlockProtectedKey(string $protectedKey, string $password): Key
    {
        Assert::notNull(
            $this->unlockProtectedKeyWillReturn,
            'What unlockProtectedKey() will return was not set'
        );

        return $this->unlockProtectedKeyWillReturn;
    }

    public function decodeSessionKey(string $encodedKey): Key
    {
        Assert::notNull(
            $this->decodeSessionKeyWillReturn,
            'What decodeSessionKey() will return was not set'
        );

        return $this->decodeSessionKeyWillReturn;
    }

    public function encryptData(string $plainText, Key $key): string
    {
        return $this->encyptDataWillReturn;
    }

    public function decryptData(string $cipherText, Key $key): string
    {
        return $this->decryptDataWillReturn;
    }

    public function updateProtectedKeyPassword(string $protectedKey, $oldPassword, $newPassword): string
    {
        $this->updateProtectedKeyPasswordWillReturn;
    }
}
