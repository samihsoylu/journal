<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework\Infrastructure\Port\PasswordHasher;

use SamihSoylu\Journal\Framework\Infrastructure\Port\PasswordHasher\Exception\InvalidPasswordException;
use SensitiveParameter;

interface PasswordHasherInterface
{
    public const MAX_PASSWORD_LENGTH = 4096;

    /**
     * Hashes a plain password.
     *
     * @throws InvalidPasswordException When the plain password is invalid, e.g. excessively long
     */
    public function hash(#[SensitiveParameter] string $plainPassword): string;

    /**
     * Verifies a plain password against a hash.
     */
    public function verify(#[SensitiveParameter] string $plainPassword, string $hashedPassword): bool;
}
