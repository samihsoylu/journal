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

class AuthenticationService
{
    protected UserRepository $repository;

    protected Encryptor $encryptor;

    private AuthenticationHelper $helper;

    public function __construct()
    {
        $this->repository = Registry::get(UserRepository::class);
        $this->encryptor  = Registry::get(Encryptor::class);
        $this->helper     = Registry::get(AuthenticationHelper::class);
    }

    public function register(string $username, string $password, string $email): void
    {
        $user = $this->repository->getByUsername($username);
        if ($user !== null) {
            throw InvalidArgumentException::alreadyRegistered('username', $username);
        }

        $user = $this->repository->getByEmailAddress($email);
        if ($user !== null) {
            throw InvalidArgumentException::alreadyRegistered('email', $email);
        }

        $encryptedPassword      = password_hash($password, PASSWORD_ARGON2ID);
        $protectedEncryptionKey = $this->encryptor->generateProtectedEncryptionKey($password);

        $user = new User();
        $user->setUsername($username)
            ->setPassword($encryptedPassword)
            ->setEmailAddress($email)
            ->setPrivilegeLevel(User::PRIVILEGE_LEVEL_USER)
            ->setEncryptionKey($protectedEncryptionKey);

        $this->repository->queue($user);
        $this->repository->save();
    }

    public function login(string $username, string $password): void
    {
        $userFailedLoginCount = $this->helper->getFailedLoginCount();
        if ($userFailedLoginCount >= 10) {
            throw InvalidOperationException::loginAttemptsExceeded($userFailedLoginCount);
        }

        $user = $this->repository->getByUsername($username);
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

    public function userIsLoggedIn(): bool
    {
        $session = UserSession::load(false);
        return !($session === null);
    }

    /**
     * Check if the current logged in user has a specific privilege level. Responds with `true` if the required level
     * is a match.
     *
     * @param int $requiredPrivilegeLevel User::PRIVILEGE_LEVEL_USER | User::PRIVILEGE_LEVEL_ADMIN
     * @return bool
     */
    public function userHasPrivilege(int $requiredPrivilegeLevel): bool
    {
        $session = UserSession::load(false);
        if ($session === null) {
            return false;
        }

        return ($session->getPrivilegeLevel() === $requiredPrivilegeLevel);
    }
}
