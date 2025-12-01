<?php

declare(strict_types=1);

namespace Tests\TestFramework\Factory;

use App\Database\Model\User;
use App\Utility\Encryptor;
use Tests\TestFramework\TestContext;

/**
 * Factory for creating User entities in tests.
 *
 * Usage:
 *   $user = UserFactory::setup()
 *       ->withUsername('testuser')
 *       ->withEmailAddress('test@example.com')
 *       ->create();
 */
final class UserFactory
{
    private string $username = 'testuser';
    private string $password = 'testpassword';
    private string $emailAddress = 'test@example.com';
    private int $privilegeLevel = User::PRIVILEGE_LEVEL_USER;
    private ?string $encryptionKey = null;
    private ?string $timezone = null;

    private function __construct() {}

    public static function setup() : self
    {
        return new self();
    }

    public function withUsername(string $username) : self
    {
        $clone = clone $this;
        $clone->username = $username;

        return $clone;
    }

    public function withPassword(string $password) : self
    {
        $clone = clone $this;
        $clone->password = $password;

        return $clone;
    }

    public function withEmailAddress(string $emailAddress) : self
    {
        $clone = clone $this;
        $clone->emailAddress = $emailAddress;

        return $clone;
    }

    public function withPrivilegeLevel(int $privilegeLevel) : self
    {
        $clone = clone $this;
        $clone->privilegeLevel = $privilegeLevel;

        return $clone;
    }

    public function withEncryptionKey(string $encryptionKey) : self
    {
        $clone = clone $this;
        $clone->encryptionKey = $encryptionKey;

        return $clone;
    }

    public function withTimezone(string $timezone) : self
    {
        $clone = clone $this;
        $clone->timezone = $timezone;

        return $clone;
    }

    public function create() : User
    {
        $user = new User();
        $user->setUsername($this->username)
            ->setPassword(password_hash($this->password, PASSWORD_ARGON2ID))
            ->setEmailAddress($this->emailAddress)
            ->setPrivilegeLevel($this->privilegeLevel)
        ;

        // Generate encryption key if not provided
        $encryptionKey = $this->encryptionKey;

        if ($encryptionKey === null) {
            $encryptor = new Encryptor();
            $encryptionKey = $encryptor->generateProtectedKey($this->password);
        }

        $user->setEncryptionKey($encryptionKey);

        if ($this->timezone !== null) {
            $user->setTimezone($this->timezone);
        }

        TestContext::$testOrm?->save($user);

        return $user;
    }

    /**
     * Get a decryption key for the user (useful for tests that need to encrypt/decrypt content).
     */
    public function getDecryptionKey() : \Defuse\Crypto\Key
    {
        $encryptor = new Encryptor();

        if ($this->encryptionKey !== null) {
            return $encryptor->getKeyFromProtectedKey($this->encryptionKey, $this->password);
        }

        // Generate a new protected key and unlock it
        $protectedKey = $encryptor->generateProtectedKey($this->password);

        return $encryptor->getKeyFromProtectedKey($protectedKey, $this->password);
    }
}
