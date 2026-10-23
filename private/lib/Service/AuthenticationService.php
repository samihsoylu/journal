<?php

declare(strict_types=1);

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Service\Helper\AuthenticationHelper;
use App\Service\Model\SessionDecorator;
use App\Service\Security\CredentialFingerprint;
use App\Service\Security\PendingLogin;
use App\Service\Security\TwoFactorChange;
use App\Service\Security\TwoFactorService;
use App\Service\Security\TwoFactorStore;
use App\Service\Security\UserKeys;
use App\Utility\UserSession;
use Defuse\Crypto\Key;

final readonly class AuthenticationService
{
    public function __construct(
        private UserRepository $repository,
        private AuthenticationHelper $helper,
        private UserSession $userSession,
        private UserKeys $keys,
        private TwoFactorStore $twoFactorStore,
        private TwoFactorService $twoFactor,
        private PendingLogin $pending,
    ) {}

    public function getUserSession() : ?UserSession
    {
        if ( ! $this->userSession->exists()) {
            return null;
        }
        $user = $this->repository->getById($this->userSession->getUserId());
        $fingerprint = $this->userSession->getCredentialFingerprint();

        if ( ! $user instanceof User || ! is_string($fingerprint) || ! hash_equals($this->credentialFingerprint($user), $fingerprint)) {
            // Password/key changes, privilege changes, and deletion revoke cached sessions.
            $this->userSession->destroy();

            return null;
        }

        return $this->userSession;
    }

    private function credentialFingerprint(User $user) : string
    {
        return CredentialFingerprint::from($user, $this->twoFactorStore->find($user->getId())?->revision);
    }

    public function login(string $username, string $password) : bool
    {
        $user = $this->repository->findByUsername($username);
        // Legacy database collations accept accent/width aliases. All names that
        // resolve to one account must share that account's throttle bucket.
        // Missing names share one per-peer budget: rotating arbitrary names
        // must not create unlimited guesses and persistent state files.
        $identity = $user instanceof User ? 'user:' . $user->getId() : 'missing';
        $this->helper->reserveLoginAttempt($identity);

        if ( ! $user instanceof User || ! password_verify($password, $user->getPassword())) {
            // Username or password is incorrect
            throw InvalidArgumentException::incorrectLogin();
        }

        $encodedEncryptionKey = $this->keys->getEncodedKeyFromProtectedKey(
            $user->getEncryptionKey(),
            $password,
        );

        $state = $this->twoFactorStore->find($user->getId());
        $fingerprint = CredentialFingerprint::from($user, $state?->revision);

        if ($state?->enabled()) {
            $this->userSession->destroy();
            $this->pending->begin($user->getId(), $fingerprint, $encodedEncryptionKey);

            return false;
        }
        $this->pending->clear();
        $this->userSession->create(
            $user->getId(),
            $user->getUsername(),
            $user->getPrivilegeLevel(),
            $encodedEncryptionKey,
            $user->getTimezone(),
            $fingerprint,
        );

        $this->helper->resetLoginAttempts($identity);

        return true;
    }

    public function completeTwoFactor(string $code, mixed $csrf) : void
    {
        $pending = $this->pending->requireState($csrf);
        $user = $this->repository->getById($pending['userId']);
        $state = $this->twoFactorStore->find($pending['userId']);

        if ( ! $user instanceof User || $state === null || ! $state->enabled()
            || ! hash_equals(CredentialFingerprint::from($user, $state->revision), $pending['fingerprint'])) {
            $this->pending->clear();

            throw TwoFactorService::invalidCode();
        }
        $encodedKey = $_COOKIE[PendingLogin::COOKIE];
        $key = $this->keys->getKeyFromEncodedKey($encodedKey);

        if ( ! $this->twoFactor->verifyProof($state, $key, $code)) {
            throw TwoFactorService::invalidCode();
        }
        $this->pending->clear();
        // Use the fingerprint actually verified, so later changes revoke this session.
        $this->userSession->create($user->getId(), $user->getUsername(), $user->getPrivilegeLevel(), $encodedKey, $user->getTimezone(), $pending['fingerprint']);
        $this->helper->resetLoginAttempts('user:' . $user->getId());
        $this->twoFactor->resetAttempts($user->getId());
    }

    public function refreshSecuritySession(TwoFactorChange $change) : void
    {
        $user = $this->repository->getById($this->userSession->getUserId());

        if ( ! $this->userSession->exists() || ! $user instanceof User || $change->state->userId !== $user->getId()) {
            throw InvalidOperationException::userIsNotLoggedIn();
        }
        $this->userSession->create($user->getId(), $user->getUsername(), $user->getPrivilegeLevel(), $this->userSession->getEncodedEncryptionKey(), $user->getTimezone(), $change->fingerprint);
    }

    public function logout() : void
    {
        $this->pending->clear();
        $this->twoFactor->cancelSetup();
        $this->userSession->destroy();
    }

    public function isUserLoggedIn() : bool
    {
        $session = $this->getUserSession();

        return $session instanceof UserSession;
    }

    public function userHasAdminPrivileges() : bool
    {
        $session = $this->getUserSession();

        if ( ! $session instanceof UserSession) {
            return false;
        }

        // 1|2 <= 2 - is true for admin and owner
        return $session->getPrivilegeLevel() <= User::PRIVILEGE_LEVEL_ADMIN;
    }

    public function getUserDecodedEncryptionKey() : Key
    {
        $session = $this->getUserSession();
        $this->ensureSessionIsNotNull($session);

        $encodedEncryptionKey = $session->getEncodedEncryptionKey();

        return $this->keys->getKeyFromEncodedKey($encodedEncryptionKey);
    }

    public function getUserId() : int
    {
        $session = $this->getUserSession();
        $this->ensureSessionIsNotNull($session);

        return $session->getUserId();
    }

    public function getSessionDecorator() : ?SessionDecorator
    {
        $session = $this->getUserSession();

        if ( ! $session instanceof UserSession) {
            return null;
        }

        return new SessionDecorator(
            $this->userHasAdminPrivileges(),
            $session->getAntiCSRFToken(),
            $session->getPrivilegeLevel(),
            $session->getTimezone(),
        );
    }

    public function updateUserSessionTimezone(string $timezone) : void
    {
        $session = $this->getUserSession();
        $this->ensureSessionIsNotNull($session);

        $this->userSession->setTimezone($timezone);
        $this->userSession->save();
    }

    private function ensureSessionIsNotNull(?UserSession $session) : void
    {
        if ( ! $session instanceof UserSession) {
            throw InvalidOperationException::userIsNotLoggedIn();
        }
    }
}
