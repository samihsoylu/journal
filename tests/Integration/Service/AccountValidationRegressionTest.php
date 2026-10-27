<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Exception\UserException;
use App\Service\AuthenticationService;
use App\Utility\UserSession;
use App\Validator\AccountValidator;
use App\Validator\CategoryValidator;
use App\Validator\EntryValidator;
use App\Validator\TemplateValidator;
use App\Validator\UserValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/** @internal
 * @coversNothing
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AccountValidationRegressionTest extends IntegrationTestCase
{
    protected function setUp() : void
    {
        session_start();
        $_SESSION = $_COOKIE = [];
        $_SERVER['REMOTE_ADDR'] = 'validation-' . bin2hex(random_bytes(12));
        parent::setUp();
        UserFactory::setup()->create();
        $this->getService(AuthenticationService::class)->login('testuser', 'testpassword');
    }

    protected function tearDown() : void
    {
        $this->getService(UserSession::class)->destroy();
        parent::tearDown();
    }

    #[Test]
    #[DataProvider('provideInvalid_fields_are_rejected_after_authenticationCases')]
    public function invalid_fields_are_rejected_after_authentication(string $class, string $action, array $fields, array $query, string $message) : void
    {
        $session = $this->getService(UserSession::class);
        $fields['form_key'] = $session->getAntiCSRFToken();
        $this->expectException(UserException::class);
        $this->expectExceptionMessage($message);
        new $class($fields, $query, $session)->validate($action);
    }

    public static function provideInvalid_fields_are_rejected_after_authenticationCases() : iterable
    {
        yield 'array timezone' => [AccountValidator::class, 'setDateTimeZone', ['timezone' => []], [], 'timezone'];
        yield 'array current password' => [AccountValidator::class, 'changePassword', ['currentPassword' => [], 'newPassword' => 'new', 'confirmPassword' => 'new'], [], 'currentPassword'];
        yield 'missing export filename' => [AccountValidator::class, 'deleteEntryExport', [], [], 'fileName'];
        yield 'empty export filename' => [AccountValidator::class, 'deleteEntryExport', ['fileName' => ''], [], 'fileName'];
        yield 'non-numeric category' => [EntryValidator::class, 'create', ['category_id' => 'wrong', 'entry_title' => 'Title', 'entry_content' => 'content'], [], 'not a number'];
        yield 'non-array order' => [CategoryValidator::class, 'setCategoryOrder', ['orderedCategoryIds' => '1,2'], [], 'must be an array'];
        yield 'invalid privilege' => [UserValidator::class, 'update', ['privilegeLevel' => '999'], [], 'privilegeLevel'];
        yield 'long template date' => [TemplateValidator::class, 'getTemplateAsJsonView', [], ['entry_date_time' => str_repeat('x', 17)], 'maximum'];
        yield 'array template date' => [TemplateValidator::class, 'getTemplateAsJsonView', [], ['entry_date_time' => []], 'invalid'];
        yield 'invalid page number' => [EntryValidator::class, 'index', [], ['page' => 'letters'], 'not a number'];
        yield 'invalid date filter' => [EntryValidator::class, 'index', [], ['date_from' => 'not-a-date'], 'incorrect date format'];
    }

    #[Test]
    public function an_expired_session_cannot_validate_an_account_change() : void
    {
        $session = $this->getService(UserSession::class);
        $token = $session->getAntiCSRFToken();
        $session->destroy();
        $this->expectException(UserException::class);
        new AccountValidator(['email' => 'new@example.test', 'form_key' => $token], [], $session)->changeEmail();
    }
}
