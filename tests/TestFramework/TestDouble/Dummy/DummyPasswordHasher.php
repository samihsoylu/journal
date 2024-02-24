<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

use SamihSoylu\Journal\Framework\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;
use SensitiveParameter;

final class DummyPasswordHasher implements PasswordHasherInterface
{
    public function hash(#[SensitiveParameter] string $plainPassword): string
    {
        return '';
    }

    public function verify(#[SensitiveParameter] string $plainPassword, string $hashedPassword): bool
    {
        return false;
    }
}
