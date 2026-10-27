<?php

declare(strict_types=1);

namespace Tests\Unit\Validator;

use App\Exception\UserException\InvalidParameterException;
use App\Validator\AuthenticationValidator;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal
 * @coversNothing
 */
final class RegistrationValidatorTest extends TestCase
{
    #[Test]
    public function registration_accepts_the_documented_boundaries() : void
    {
        foreach ([['abcd', 'p'], [str_repeat('a', 64), str_repeat('p', 1024)]] as [$username, $password]) {
            new AuthenticationValidator(['username' => $username, 'password' => $password, 'email' => 'reader@example.test'])->validate('register');
        }
        $this->addToAssertionCount(2);
    }

    #[Test]
    #[DataProvider('provideMalformed_registration_fields_are_validation_errorsCases')]
    public function malformed_registration_fields_are_validation_errors(array $overrides, string $message) : void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage($message);
        new AuthenticationValidator([...['username' => 'reader', 'password' => 'password', 'email' => 'reader@example.test'], ...$overrides])->register();
    }

    public static function provideMalformed_registration_fields_are_validation_errorsCases() : iterable
    {
        yield 'short username' => [['username' => 'abc'], 'at least'];
        yield 'long username' => [['username' => str_repeat('a', 65)], 'maximum'];
        yield 'array username' => [['username' => []], 'invalid'];
        yield 'empty password' => [['password' => ''], 'at least'];
        yield 'long password' => [['password' => str_repeat('p', 1025)], 'maximum'];
        yield 'array password' => [['password' => []], 'invalid'];
        yield 'missing email value' => [['email' => ''], 'invalid'];
        yield 'malformed email' => [['email' => 'reader@'], 'invalid'];
    }

    #[Test]
    public function unknown_validation_action_is_rejected() : void
    {
        $this->expectException(LogicException::class);
        new AuthenticationValidator([])->validate('unknownAction');
    }
}
