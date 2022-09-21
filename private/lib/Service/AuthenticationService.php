<?php declare(strict_types=1);

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Service\Model\SessionDecorator;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Service\Helper\AuthenticationHelper;
use App\Utility\Encryptor;
use App\Utility\Registry;
use App\Utility\UserSession;
use Defuse\Crypto\Key;

class AuthenticationService
{
    private UserRepository $repository;
    private AuthenticationHelper $helper;

    public function __construct()
    {
        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);
        $this->repository = $repository;

        $this->helper = new AuthenticationHelper();
    }

    public function getUserSession(): ?UserSession
    {
        return UserSession::load();
    }

    public function login(string $username, string $password): void
    {
        $userFailedLoginCount = $this->helper->getFailedLoginCount();
        if ($userFailedLoginCount >= 10) {
            throw InvalidOperationException::loginAttemptsExceeded($userFailedLoginCount);
        }

        $user = $this->repository->findByUsername($username);
        if ($user === null || !password_verify($password, $user->getPassword())) {
            $this->helper->setFailedLoginCount(++$userFailedLoginCount);

            // Username or password is incorrect
            throw InvalidArgumentException::incorrectLogin();
        }

        $encryptor = new Encryptor();
        $encodedEncryptionKey = $encryptor->getEncodedKeyFromProtectedKey(
            $user->getEncryptionKey(),
            $password
        );

        UserSession::create(
            $user->getId(),
            $user->getUsername(),
            $user->getPrivilegeLevel(),
            $encodedEncryptionKey
        );

        $this->helper->setFailedLoginCount(0);
    }

    public function logout(): void
    {
        UserSession::destroy();
    }

    public function isUserLoggedIn(): bool
    {
        $session = $this->getUserSession();

        return ($session !== null);
    }

    public function userHasAdminPrivileges(): bool
    {
        $session = $this->getUserSession();
        if ($session === null) {
            return false;
        }

        // 1|2 <= 2 - is true for admin and owner
        return ($session->getPrivilegeLevel() <= User::PRIVILEGE_LEVEL_ADMIN);
    }

    public function getUserDecodedEncryptionKey(): Key
    {
        $session = $this->getUserSession();
        $this->ensureSessionIsNotNull($session);

        $encodedEncryptionKey = $session->getEncodedEncryptionKey();

        $encryptor = new Encryptor();

        return $encryptor->getKeyFromEncodedKey($encodedEncryptionKey);
    }

    public function getUserId(): int
    {
        $session = $this->getUserSession();
        $this->ensureSessionIsNotNull($session);

        return $session->getUserId();
    }

    public function getSessionDecorator(): ?SessionDecorator
    {
        $session = $this->getUserSession();
        if ($session === null) {
            return null;
        }

        return new SessionDecorator(
            $this->userHasAdminPrivileges(),
            $session->getAntiCSRFToken(),
            $session->getPrivilegeLevel()
        );
    }

    private function ensureSessionIsNotNull(?UserSession $session): void
    {
        if ($session === null) {
            throw InvalidOperationException::userIsNotLoggedIn();
        }
    }
}
