<?php

declare(strict_types=1);

namespace Tests\Unit\Validator;

use App\Exception\UserException\InvalidParameterException;
use App\Utility\Session;
use App\Validator\AuthenticationValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(AuthenticationValidator::class)]
final class AuthenticationValidatorTest extends TestCase
{
    private array $previousSession;

    protected function setUp() : void
    {
        parent::setUp();
        $this->previousSession = $_SESSION ?? [];
        $_SESSION = [];
    }

    protected function tearDown() : void
    {
        $_SESSION = $this->previousSession;
        parent::tearDown();
    }

    #[Test]
    #[DataProvider('provideInvalid_tokens_are_validation_errors_instead_of_type_errorsCases')]
    public function invalid_tokens_are_validation_errors_instead_of_type_errors(mixed $sessionToken, mixed $postedToken) : void
    {
        if ($sessionToken !== null) {
            Session::put('login_form_key', $sessionToken);
        }
        $validator = new AuthenticationValidator(['username' => 'testuser', 'password' => 'testpassword', 'form_key' => $postedToken]);
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Invalid Form Key. Please try again.');
        $validator->login();
    }

    public static function provideInvalid_tokens_are_validation_errors_instead_of_type_errorsCases() : iterable
    {
        return [
            'expired or absent session' => [null, 'submitted-token'],
            'array session token' => [[], 'submitted-token'],
            'integer session token' => [123, 'submitted-token'],
            'array posted token' => ['session-token', ['session-token']],
            'null posted token' => ['session-token', null],
            'integer posted token' => ['123', 123],
            'different tokens' => ['session-token', 'different-token'],
            'empty tokens' => ['', ''],
            'empty posted token' => ['session-token', ''],
        ];
    }

    #[Test]
    public function matching_string_tokens_allow_login_validation() : void
    {
        Session::put('login_form_key', 'valid-session-token');
        $this->expectNotToPerformAssertions();
        new AuthenticationValidator(['username' => 'testuser', 'password' => 'testpassword', 'form_key' => 'valid-session-token'])->login();
    }

    #[Test]
    public function a_missing_posted_token_is_a_required_field_error() : void
    {
        Session::put('login_form_key', 'valid-session-token');
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Field form_key is required');
        new AuthenticationValidator(['username' => 'testuser', 'password' => 'testpassword'])->login();
    }
}
