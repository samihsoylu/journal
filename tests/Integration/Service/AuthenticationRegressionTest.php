<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Exception\UserException\InvalidParameterException;
use App\Service\AuthenticationService;
use App\Service\UserService;
use App\Utility\Cache;
use App\Utility\UserSession;
use App\Validator\AccountValidator;
use App\Validator\AuthenticationValidator;
use App\Validator\CategoryValidator;
use App\Validator\EntryValidator;
use App\Validator\MediaValidator;
use App\Validator\TemplateValidator;
use App\Validator\UserValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class AuthenticationRegressionTest extends IntegrationTestCase
{
    protected function setUp() : void
    {
        session_start();
        $_SESSION = [];
        $_COOKIE = [];
        $_SERVER['REMOTE_ADDR'] = 'test-' . bin2hex(random_bytes(12));
        parent::setUp();
    }

    protected function tearDown() : void
    {
        $this->getService(UserSession::class)->destroy();
        $this->getService(Cache::class)->delete(md5($_SERVER['REMOTE_ADDR']));
        parent::tearDown();
    }

    #[Test]
    public function changing_unknown_usernames_does_not_create_unlimited_login_budgets() : void
    {
        UserFactory::setup()->create();
        $auth = $this->getService(AuthenticationService::class);

        for ($attempt = 0; $attempt < 10; ++$attempt) {
            try {
                $auth->login('missing-user-' . $attempt, 'wrong-password');
                self::fail('A missing account authenticated');
            } catch (InvalidArgumentException) {
                self::assertFalse($auth->isUserLoggedIn());
            }
        }
        // A real account behind the same proxy can still log in, but cannot
        // clear the separate budget for guesses at nonexistent accounts.
        $auth->login('testuser', 'testpassword');
        self::assertTrue($auth->isUserLoggedIn());
        $this->expectException(InvalidOperationException::class);
        $auth->login('another-missing-user', 'wrong-password');
    }

    #[Test]
    public function login_unlocks_the_existing_key_and_logout_revokes_the_session() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $auth = $this->getService(AuthenticationService::class);
        self::assertFalse($auth->isUserLoggedIn());
        $anonymousSessionId = session_id();
        $auth->login('testuser', 'testpassword');
        self::assertNotSame($anonymousSessionId, session_id());
        self::assertTrue($auth->isUserLoggedIn());
        self::assertSame($user->getId(), $auth->getUserId());
        self::assertSame($factory->getDecryptionKey()->saveToAsciiSafeString(), $auth->getUserDecodedEncryptionKey()->saveToAsciiSafeString());
        $auth->logout();
        self::assertFalse($auth->isUserLoggedIn());
        self::assertArrayNotHasKey('SessionID', $_SESSION);
    }

    #[Test]
    public function an_invalid_password_does_not_create_a_session() : void
    {
        UserFactory::setup()->create();
        $auth = $this->getService(AuthenticationService::class);
        $anonymousSessionId = session_id();

        try {
            $auth->login('testuser', 'wrong-password');
            self::fail('Invalid password was accepted');
        } catch (InvalidArgumentException) {
            self::assertSame($anonymousSessionId, session_id());
            self::assertFalse($auth->isUserLoggedIn());
            self::assertArrayNotHasKey('SessionID', $_SESSION);
        }
    }

    #[Test]
    public function a_following_request_restores_identity_without_persisting_the_unlocked_key() : void
    {
        UserFactory::setup()->create();
        $this->getService(AuthenticationService::class)->login('testuser', 'testpassword');
        $session = $this->getService(UserSession::class);
        $stored = $this->getService(Cache::class)->getItem($session->getSessionId())->get();
        self::assertArrayNotHasKey('EEK', $stored);
        $_COOKIE['EEK'] = $session->getEncodedEncryptionKey();
        $restored = new UserSession($this->getService(Cache::class));
        self::assertTrue($restored->exists());
        self::assertSame($session->getUserId(), $restored->getUserId());
        self::assertSame($session->getAntiCSRFToken(), $restored->getAntiCSRFToken());
    }

    #[Test]
    public function an_expired_cache_entry_cannot_restore_an_authenticated_session() : void
    {
        $_SESSION['SessionID'] = 'missing-' . bin2hex(random_bytes(12));
        $_COOKIE['EEK'] = 'present-but-untrusted';
        self::assertFalse(new UserSession($this->getService(Cache::class))->exists());
    }

    #[Test]
    public function a_cached_session_without_its_csrf_binding_cannot_be_restored() : void
    {
        UserFactory::setup()->create();
        $auth = $this->getService(AuthenticationService::class);
        $auth->login('testuser', 'testpassword');
        $session = $this->getService(UserSession::class);
        $_COOKIE['EEK'] = $session->getEncodedEncryptionKey();
        self::assertSame(User::PRIVILEGE_LEVEL_USER, $session->getPrivilegeLevel());
        unset($_SESSION['AntiCSRFToken']);
        self::assertFalse(new UserSession($this->getService(Cache::class))->exists());
    }

    #[Test]
    public function a_form_without_a_csrf_token_is_rejected() : void
    {
        UserFactory::setup()->create();
        $this->getService(AuthenticationService::class)->login('testuser', 'testpassword');
        $session = $this->getService(UserSession::class);
        $this->expectException(InvalidParameterException::class);
        new EntryValidator(['category_id' => '1', 'entry_title' => 'Valid title', 'entry_content' => 'Content'], [], $session)->create();
    }

    #[Test]
    public function csrf_token_is_required_and_a_successful_form_rotates_it() : void
    {
        UserFactory::setup()->create();
        $this->getService(AuthenticationService::class)->login('testuser', 'testpassword');
        $session = $this->getService(UserSession::class);
        $post = ['category_id' => '1', 'entry_title' => 'Valid title', 'entry_content' => 'Content', 'form_key' => 'invalid'];

        try {
            new EntryValidator($post, [], $session)->create();
            self::fail('Invalid CSRF token was accepted');
        } catch (InvalidParameterException) {
            $post['form_key'] = $session->getAntiCSRFToken();
        }
        new EntryValidator($post, [], $session)->create();
        self::assertNotSame($post['form_key'], $session->getAntiCSRFToken());
        $this->expectException(InvalidParameterException::class);
        new EntryValidator($post, [], $session)->create();
    }

    #[Test]
    public function changing_a_password_revokes_an_existing_session_without_changing_the_content_key() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $auth = $this->getService(AuthenticationService::class);
        $auth->login('testuser', 'testpassword');
        $key = $auth->getUserDecodedEncryptionKey()->saveToAsciiSafeString();
        $this->getService(UserService::class)->changePassword($user->getId(), 'testpassword', 'changed-password');
        self::assertFalse($auth->isUserLoggedIn());
        self::assertArrayNotHasKey('SessionID', $_SESSION);
        $auth->login('testuser', 'changed-password');
        self::assertSame($key, $auth->getUserDecodedEncryptionKey()->saveToAsciiSafeString());
    }

    #[Test]
    public function demoting_an_admin_revokes_cached_administrator_rights() : void
    {
        $user = UserFactory::setup()->withPrivilegeLevel(User::PRIVILEGE_LEVEL_ADMIN)->create();
        $auth = $this->getService(AuthenticationService::class);
        $auth->login('testuser', 'testpassword');
        self::assertTrue($auth->userHasAdminPrivileges());
        $user->setPrivilegeLevel(User::PRIVILEGE_LEVEL_USER);
        $this->getRepository(UserRepository::class)->save();
        self::assertFalse($auth->userHasAdminPrivileges());
        self::assertFalse($auth->isUserLoggedIn());
    }

    #[Test]
    public function deleting_an_account_revokes_its_cached_session() : void
    {
        $user = UserFactory::setup()->create();
        $auth = $this->getService(AuthenticationService::class);
        $auth->login('testuser', 'testpassword');
        $repository = $this->getRepository(UserRepository::class);
        $repository->remove($user);
        $repository->save();
        self::assertFalse($auth->isUserLoggedIn());
        self::assertArrayNotHasKey('SessionID', $_SESSION);
    }

    #[Test]
    #[DataProvider('provideEvery_mutation_rejects_missing_wrong_and_malformed_tokensCases')]
    public function every_mutation_rejects_missing_wrong_and_malformed_tokens(string $action, mixed $token) : void
    {
        UserFactory::setup()->create();
        $this->getService(AuthenticationService::class)->login('testuser', 'testpassword');
        $session = $this->getService(UserSession::class);
        $post = ['form_key' => $token, 'timezone' => 'Europe/Amsterdam', 'orderedCategoryIds' => [1], 'category_id' => '1', 'entry_title' => 'Valid title', 'entry_content' => 'Valid content'];
        $this->expectException(InvalidParameterException::class);

        match ($action) {
            'timezone' => new AccountValidator($post, [], $session)->setDateTimeZone(),
            'sort' => new CategoryValidator($post, [], $session)->setCategoryOrder(),
            'upload' => new MediaValidator($post, [], $session)->upload(),
            'entry' => new EntryValidator($post, [], $session)->create(),
            'delete-entry' => new EntryValidator($post, [], $session)->delete(),
            'delete-template' => new TemplateValidator($post, [], $session)->delete(),
            'delete-category' => new CategoryValidator($post, [], $session)->delete(),
            'delete-user' => new UserValidator($post, [], $session)->delete(),
            'logout' => new AuthenticationValidator($post, [], $session)->logout(),
            default => self::fail('Unknown mutation test action'),
        };
    }

    public static function provideEvery_mutation_rejects_missing_wrong_and_malformed_tokensCases() : iterable
    {
        foreach ([null, 'incorrect-token', ['array-token'], 123] as $index => $token) {
            foreach (['timezone', 'sort', 'upload', 'entry', 'delete-entry', 'delete-template', 'delete-category', 'delete-user', 'logout'] as $action) {
                yield $action . '-' . $index => [$action, $token];
            }
        }
    }

    #[Test]
    public function ajax_uploads_and_sorting_do_not_invalidate_the_open_entry_form() : void
    {
        UserFactory::setup()->create();
        $this->getService(AuthenticationService::class)->login('testuser', 'testpassword');
        $session = $this->getService(UserSession::class);
        $token = $session->getAntiCSRFToken();
        new MediaValidator(['form_key' => $token], [], $session)->upload();
        new MediaValidator(['form_key' => $token], [], $session)->upload();
        new CategoryValidator(['form_key' => $token, 'orderedCategoryIds' => [1, 2]], [], $session)->setCategoryOrder();
        self::assertSame($token, $session->getAntiCSRFToken());
        new EntryValidator(['form_key' => $token, 'category_id' => '1', 'entry_title' => 'Valid title', 'entry_content' => 'Content'], [], $session)->create();
        self::assertNotSame($token, $session->getAntiCSRFToken());
    }

    #[Test]
    public function collation_aliases_share_one_account_limit_and_other_logins_do_not_reset_it() : void
    {
        $user = UserFactory::setup()->create();
        UserFactory::setup()->withUsername('other-user')->withEmailAddress('other@example.test')->create();
        $auth = $this->getService(AuthenticationService::class);
        $repository = $this->getRepository(UserRepository::class);
        // Connection charsets/collations differ in Unicode equivalence. Only names resolving to
        // this account must consume its bucket; unknown names have their own.
        $aliases = array_values(array_filter(['testuser', 'TESTUSER', 'téstuser', 'ｔｅｓｔｕｓｅｒ'], static fn ($name) => $repository->findByUsername($name)?->getId() === $user->getId()));
        self::assertContains('testuser', $aliases);
        self::assertContains('TESTUSER', $aliases);

        for ($attempt = 0; $attempt < 10; ++$attempt) {
            try {
                $auth->login($aliases[$attempt % count($aliases)], 'wrong-password');
                self::fail('Invalid password was accepted');
            } catch (InvalidArgumentException) {
                self::assertFalse($auth->isUserLoggedIn());
            }
        }
        $auth->login('other-user', 'testpassword');
        self::assertTrue($auth->isUserLoggedIn());
        $auth->logout();

        try {
            $auth->login('testuser', 'testpassword');
            self::fail('A successful login to a different account reset the victim limit');
        } catch (InvalidOperationException) {
            self::assertFalse($auth->isUserLoggedIn());
        }
        $_SERVER['REMOTE_ADDR'] .= '-other-peer';
        $auth->login($aliases[1], 'testpassword');
        self::assertTrue($auth->isUserLoggedIn());
    }

    #[Test]
    public function old_short_passwords_still_log_in() : void
    {
        UserFactory::setup()->withPassword('old')->create();
        $auth = $this->getService(AuthenticationService::class);
        $auth->login('testuser', 'old');
        self::assertTrue($auth->isUserLoggedIn());
    }
}
