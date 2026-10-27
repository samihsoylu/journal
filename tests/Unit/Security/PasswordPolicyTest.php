<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Exception\UserException\InvalidArgumentException;
use App\Service\Security\PasswordPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(PasswordPolicy::class)]
final class PasswordPolicyTest extends TestCase
{
    #[Test]
    #[DataProvider('provideNew_passwords_follow_the_length_policyCases')]
    public function new_passwords_follow_the_length_policy(string $password) : void
    {
        $this->expectException(InvalidArgumentException::class);
        PasswordPolicy::validate($password);
    }

    public static function provideNew_passwords_follow_the_length_policyCases() : iterable
    {
        yield 'empty' => [''];
        yield 'eleven characters' => ['12345678901'];
        yield 'unicode characters count rather than bytes' => [str_repeat('é', 11)];
        yield 'too many bytes' => [str_repeat('x', 1025)];
        yield 'invalid utf8' => [str_repeat("\xff", 12)];
    }

    #[Test]
    public function passphrases_unicode_and_the_upper_boundary_are_accepted() : void
    {
        foreach (['123456789012', 'a calm long passphrase', str_repeat('é', 12), str_repeat('x', 1024)] as $password) {
            PasswordPolicy::validate($password);
        }
        $this->addToAssertionCount(4);
    }
}
