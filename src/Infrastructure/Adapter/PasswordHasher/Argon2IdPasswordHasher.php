<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Infrastructure\Adapter\PasswordHasher;

use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\Exception\InvalidPasswordException;
use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;
use SensitiveParameter;

final class Argon2IdPasswordHasher implements PasswordHasherInterface
{
    public function hash(#[SensitiveParameter] string $plainPassword): string
    {
        if (strlen($plainPassword) > self::MAX_PASSWORD_LENGTH) {
            throw new InvalidPasswordException(
                sprintf("Password length cannot exceed %s characters", self::MAX_PASSWORD_LENGTH)
            );
        }

        return password_hash($plainPassword, PASSWORD_ARGON2ID);
    }

    public function verify(#[SensitiveParameter] string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}