<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidParameterException;
use App\Service\AuthenticationService;
use App\Utility\Cache;
use App\Utility\UserSession;
use App\Validator\EntryValidator;
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
}
