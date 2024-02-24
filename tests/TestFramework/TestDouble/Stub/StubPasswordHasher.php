<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub;

use SamihSoylu\Journal\Framework\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;
use SensitiveParameter;

final class StubPasswordHasher implements PasswordHasherInterface
{
    public function __construct(
        private string $hashWillReturn = '',
        private bool $verifyWillReturn = false,
    ) {}

    public function hash(#[SensitiveParameter] string $plainPassword): string
    {
        return $this->hashWillReturn;
    }

    public function verify(#[SensitiveParameter] string $plainPassword, string $hashedPassword): bool
    {
        return $this->verifyWillReturn;
    }
}
