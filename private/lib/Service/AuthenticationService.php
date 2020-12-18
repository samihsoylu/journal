<?php declare(strict_types=1);

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidOperationException;
use App\Service\Helpers\AuthenticationHelper;
use App\Utility\Encryptor;
use App\Utility\Registry;
use App\Utility\UserSession;
use App\Exception\UserException\InvalidArgumentException;
use Defuse\Crypto\Key;

class AuthenticationService
{
    private UserRepository $repository;
    private Encryptor $encryptor;
    private AuthenticationHelper $helper;

    public function __construct()
    {
        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);
        $this->repository = $repository;

        $this->helper     = new AuthenticationHelper();
        $this->encryptor  = new Encryptor();
    }

    public function login(string $username, string $password): void
    {
        $userFailedLoginCount = $this->helper->getFailedLoginCount();
        if ($userFailedLoginCount >= 10) {
            throw InvalidOperationException::loginAttemptsExceeded($userFailedLoginCount);
        }

        $user = $this->repository->findByUsername($username);
        if ($user === null || !password_verify($password, $user->getPassword())) {
            $this->helper->setFailedLoginCount($userFailedLoginCount + 1);

            // Username or password is incorrect
            throw InvalidArgumentException::incorrectLogin();
        }

        $encodedEncryptionKey = $this->encryptor->getEncodedEncryptionKeyFromProtectedEncryptionKey(
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
        try {
            $userSession = $this->getUserSession();
        } catch(InvalidOperationException $e) {
            return false;
        }

        return true;
    }

    /**
     * Check if the current logged in user has a specific privilege level. Responds with `true` if the required level
     * is a match.
     *
     * @param int $requiredPrivilegeLevel User::PRIVILEGE_LEVEL_USER | User::PRIVILEGE_LEVEL_ADMIN
     * @return bool
     */
    public function userHasPrivilegeLevel(int $requiredPrivilegeLevel): bool
    {
        return ($this->getUserSession() === $requiredPrivilegeLevel);
    }

    public function getUserSession(): UserSession
    {
        $userSession = UserSession::load();
        if ($userSession === null) {
            throw InvalidOperationException::userIsNotLoggedIn();
        }

        return $userSession;
    }

    public function getNotRequiredUserSession(): ?UserSession
    {
        try {
            return $this->getUserSession();
        } catch (InvalidOperationException $e) {
            return null;
        }
    }

    public function userHasAdminPrivileges(): bool
    {
        // 1|2 <= 2 - is true for admin and owner
        return ($this->getUserSession()->getPrivilegeLevel() <= User::PRIVILEGE_LEVEL_ADMIN);
    }

    public function getUserDecodedEncryptionKey(): Key
    {
        $encodedEncryptionKey = $this->getUserSession()->getEncodedEncryptionKey();

        $encryptor = new Encryptor();
        return $encryptor->getKeyFromEncodedEncryptionKey($encodedEncryptionKey);
    }
}
