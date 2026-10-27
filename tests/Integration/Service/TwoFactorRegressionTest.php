<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidOperationException;
use App\Exception\UserException\InvalidParameterException;
use App\Service\AuthenticationService;
use App\Service\Helper\AuthenticationHelper;
use App\Service\Security\CredentialFingerprint;
use App\Service\Security\PendingLogin;
use App\Service\Security\RecoveryCodes;
use App\Service\Security\TwoFactorService;
use App\Service\Security\TwoFactorStore;
use App\Service\UserService;
use App\Utility\Encryptor;
use App\Utility\UserSession;
use Defuse\Crypto\Key;
use Doctrine\ORM\EntityManagerInterface;
use OTPHP\TOTP;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class TwoFactorRegressionTest extends IntegrationTestCase
{
    private MockClock $clock;
    private User $user;
    private Key $key;
    private TwoFactorService $service;
    private TwoFactorStore $store;
    private AuthenticationService $auth;

    protected function setUp() : void
    {
        session_start();
        $_SESSION = $_COOKIE = [];
        $_SERVER['REMOTE_ADDR'] = 'two-factor-test-' . bin2hex(random_bytes(12));
        parent::setUp();
        $this->clock = new MockClock('2030-01-01 12:00:00 UTC');
        $this->getContainer()->set(ClockInterface::class, $this->clock);
        $factory = UserFactory::setup();
        $this->user = $factory->create();
        $this->key = $factory->getDecryptionKey();
        $this->service = $this->getService(TwoFactorService::class);
        $this->store = $this->getService(TwoFactorStore::class);
        $this->auth = $this->getService(AuthenticationService::class);
    }

    protected function tearDown() : void
    {
        $this->auth->logout();
        $this->service->resetAttempts($this->user->getId());
        parent::tearDown();
    }

    private function code(string $secret, int $offset = 0) : string
    {
        return TOTP::createFromSecret($secret, $this->clock)->at($this->clock->now()->getTimestamp() + $offset);
    }

    private function enroll() : array
    {
        $this->service->beginSetup($this->user->getId(), $this->key, 'testpassword');
        $setup = $this->service->setup($this->user->getId(), $this->key);
        $change = $this->service->confirm($this->user->getId(), $this->key, $this->code($setup['secret'], -30));

        return [$setup['secret'], $change];
    }

    private function beginLogin(string $password = 'testpassword') : array
    {
        self::assertFalse($this->auth->login('testuser', $password));
        $_COOKIE[PendingLogin::COOKIE] = $this->key->saveToAsciiSafeString();
        $pending = $this->getService(PendingLogin::class)->state();
        self::assertNotNull($pending);
        self::assertFalse($this->auth->isUserLoggedIn());
        self::assertArrayNotHasKey('SessionID', $_SESSION);
        self::assertArrayNotHasKey('EEK', $_COOKIE);

        return $pending;
    }

    public function test_enrolled_accounts_cannot_start_another_setup() : void
    {
        $this->enroll();
        $this->expectException(InvalidParameterException::class);
        $this->service->beginSetup($this->user->getId(), $this->key, 'testpassword');
    }

    public function test_overlong_proofs_and_a_wrong_content_key_cannot_consume_a_factor() : void
    {
        [$secret] = $this->enroll();
        $state = $this->store->find($this->user->getId());
        self::assertFalse($this->service->verifyProof($state, $this->key, str_repeat('x', 129)));
        self::assertFalse($this->service->verifyProof($state, Key::createNewRandomKey(), $this->code($secret)));
        self::assertTrue($this->service->verifyProof($state, $this->key, $this->code($secret)));
    }

    public function test_existing_users_remain_password_only_and_enrollment_keeps_ciphertext_private() : void
    {
        self::assertNull($this->store->find($this->user->getId()));
        self::assertTrue($this->auth->login('testuser', 'testpassword'));
        $this->service->beginSetup($this->user->getId(), $this->key, 'testpassword');
        $setup = $this->service->setup($this->user->getId(), $this->key);
        self::assertStringNotContainsString($setup['secret'], serialize($_SESSION));
        self::assertStringNotContainsString($this->key->saveToAsciiSafeString(), serialize($_SESSION));
        self::assertFalse($this->store->find($this->user->getId())?->enabled() ?? false);
        $change = $this->service->confirm($this->user->getId(), $this->key, $this->code($setup['secret'], -30));
        self::assertCount(10, $change->codes);
        $stored = $this->testOrm->fetchOneAssoc('SELECT encryptedSecret FROM user_two_factor WHERE userId = ?', [$this->user->getId()]);
        self::assertNotSame($setup['secret'], $stored['encryptedSecret']);
        self::assertSame($setup['secret'], $this->getService(Encryptor::class)->decrypt($stored['encryptedSecret'], $this->key));
        $hashes = $this->testOrm->fetchAllAssoc('SELECT codeHash FROM user_two_factor_recovery WHERE userId = ?', [$this->user->getId()]);
        self::assertCount(10, $hashes);
        self::assertStringNotContainsString($change->codes[0], serialize($hashes) . serialize($_SESSION));
        self::assertNull($this->service->setup($this->user->getId(), $this->key));
        self::assertFalse($this->auth->isUserLoggedIn());
    }

    public function test_login_needs_a_fresh_code_and_rejects_replay() : void
    {
        [$secret] = $this->enroll();
        $pending = $this->beginLogin();

        try {
            $this->auth->completeTwoFactor($this->code($secret, -30), $pending['csrf']);
            self::fail('Enrollment code replay was accepted');
        } catch (InvalidParameterException) {
            self::assertFalse($this->auth->isUserLoggedIn());
        }
        $this->auth->completeTwoFactor($this->code($secret), $pending['csrf']);
        self::assertTrue($this->auth->isUserLoggedIn());
        self::assertSame($this->key->saveToAsciiSafeString(), $this->auth->getUserDecodedEncryptionKey()->saveToAsciiSafeString());
        self::assertArrayNotHasKey('pending_login', $_SESSION);
        $this->auth->logout();
        $pending = $this->beginLogin();
        $this->expectException(InvalidParameterException::class);
        $this->auth->completeTwoFactor($this->code($secret), $pending['csrf']);
    }

    public function test_recovery_is_one_use_and_regeneration_replaces_old_codes_without_lowering_the_counter() : void
    {
        [$secret, $change] = $this->enroll();
        $state = $change->state;
        self::assertTrue($this->service->verifyProof($state, $this->key, $change->codes[0]));
        self::assertFalse($this->service->verifyProof($state, $this->key, $change->codes[0]));
        self::assertTrue($this->service->verifyProof($state, $this->key, $this->code($secret, 30)));
        $future = $this->store->find($this->user->getId())->lastCounter;
        $replacement = $this->service->regenerate($this->user->getId(), $this->key, 'testpassword', $change->codes[1]);
        self::assertNotSame($state->revision, $replacement->state->revision);
        self::assertSame($future, $replacement->state->lastCounter);
        self::assertFalse($this->service->verifyProof($replacement->state, $this->key, $change->codes[2]));
        self::assertFalse($this->store->consumeRecovery($this->user->getId(), $state->revision, $this->getService(RecoveryCodes::class)->hash($replacement->codes[0])));
        self::assertFalse($this->store->consumeCounter($this->user->getId(), $state->revision, $future + 1));
        self::assertTrue($this->service->verifyProof($replacement->state, $this->key, $replacement->codes[0]));
        self::assertFalse($this->service->verifyProof($replacement->state, $this->key, $this->code($secret, 30)));
    }

    public function test_enable_then_disable_never_revives_an_older_password_only_session() : void
    {
        $this->auth->login('testuser', 'testpassword');
        $oldSession = clone $this->getService(UserSession::class);
        $old = new AuthenticationService($this->getRepository(UserRepository::class), $this->getService(AuthenticationHelper::class), $oldSession, $this->getService(Encryptor::class), $this->store, $this->service, $this->getService(PendingLogin::class));
        [, $change] = $this->enroll();
        $disabled = $this->service->disable($this->user->getId(), $this->key, 'testpassword', $change->codes[0]);
        self::assertFalse($disabled->state->enabled());
        self::assertNotSame($change->state->revision, $disabled->state->revision);
        self::assertNotSame(CredentialFingerprint::from($this->user, null), CredentialFingerprint::from($this->user, $disabled->state->revision));
        self::assertFalse($old->isUserLoggedIn());
        self::assertTrue($this->auth->login('testuser', 'testpassword'));
    }

    public function test_changed_password_revokes_pending_login_and_preserves_the_authenticator_secret() : void
    {
        [$secret] = $this->enroll();
        $pending = $this->beginLogin();
        $this->getService(UserService::class)->changePassword($this->user->getId(), 'testpassword', 'new-long-password');

        try {
            $this->auth->completeTwoFactor($this->code($secret), $pending['csrf']);
            self::fail('Changed credential challenge was accepted');
        } catch (InvalidParameterException) {
            self::assertArrayNotHasKey('pending_login', $_SESSION);
        }
        $pending = $this->beginLogin('new-long-password');
        $this->auth->completeTwoFactor($this->code($secret), $pending['csrf']);
        self::assertTrue($this->auth->isUserLoggedIn());
    }

    public function test_setup_expiry_and_changed_credentials_cannot_enable_a_factor() : void
    {
        $this->service->beginSetup($this->user->getId(), $this->key, 'testpassword');
        $this->clock->sleep(600);
        self::assertNull($this->service->setup($this->user->getId(), $this->key));
        $this->service->beginSetup($this->user->getId(), $this->key, 'testpassword');
        $this->user->setPrivilegeLevel(User::PRIVILEGE_LEVEL_ADMIN);
        $this->getRepository(UserRepository::class)->save();
        self::assertNull($this->service->setup($this->user->getId(), $this->key));
        self::assertNull($this->store->find($this->user->getId()));
        $this->expectException(InvalidParameterException::class);
        $this->service->confirm($this->user->getId(), $this->key, '000000');
    }

    public function test_restarting_password_login_and_changing_peers_does_not_reset_the_factor_budget() : void
    {
        [, $change] = $this->enroll();

        for ($attempt = 0; $attempt < 10; ++$attempt) {
            $_SERVER['REMOTE_ADDR'] = 'different-peer-' . $attempt;
            $pending = $this->beginLogin();

            try {
                $this->auth->completeTwoFactor('not-a-code', $pending['csrf']);
                self::fail('Wrong factor was accepted');
            } catch (InvalidParameterException) {
                self::assertFalse($this->auth->isUserLoggedIn());
            }
        }
        $_SERVER['REMOTE_ADDR'] = 'another-peer';
        $pending = $this->beginLogin();
        $this->expectException(InvalidOperationException::class);
        $this->auth->completeTwoFactor($change->codes[0], $pending['csrf']);
    }

    public function test_expected_revisions_reject_stale_configuration_and_counter_updates() : void
    {
        [, $change] = $this->enroll();
        $disabled = $this->service->disable($this->user->getId(), $this->key, 'testpassword', $change->codes[0]);
        self::assertFalse($this->store->consumeCounter($this->user->getId(), $change->state->revision, PHP_INT_MAX));
        self::assertFalse($this->store->consumeRecovery($this->user->getId(), $change->state->revision, $this->getService(RecoveryCodes::class)->hash($change->codes[1])));
        $this->expectException(InvalidParameterException::class);
        $this->store->configure($this->user->getId(), $change->state->revision, 'stale-secret', null, [], $change->fingerprint);
    }

    public function test_settings_transaction_rechecks_the_verified_account_credentials_under_its_row_lock() : void
    {
        [, $change] = $this->enroll();
        $this->getService(EntityManagerInterface::class)->getConnection()->executeStatement('UPDATE users SET privilegeLevel = ? WHERE id = ?', [User::PRIVILEGE_LEVEL_ADMIN, $this->user->getId()]);
        self::assertSame($change->state->revision, $this->store->find($this->user->getId())->revision);

        try {
            $this->store->configure($this->user->getId(), $change->state->revision, null, null, [], $change->fingerprint);
            self::fail('A captured proof changed settings after account credentials changed');
        } catch (InvalidParameterException) {
            self::assertTrue($this->store->find($this->user->getId())->enabled());
            self::assertSame($change->state->revision, $this->store->find($this->user->getId())->revision);
        }
    }

    public function test_session_refresh_cannot_authorize_a_newer_unproved_credential_snapshot() : void
    {
        $this->auth->login('testuser', 'testpassword');
        [, $change] = $this->enroll();
        $this->user->setPrivilegeLevel(User::PRIVILEGE_LEVEL_ADMIN);
        $this->getRepository(UserRepository::class)->save();
        $this->auth->refreshSecuritySession($change);
        self::assertFalse($this->auth->isUserLoggedIn());
        self::assertArrayNotHasKey('SessionID', $_SESSION);
    }
}
