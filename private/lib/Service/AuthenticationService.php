<?php declare(strict_types=1);

namespace App\Service;

use App\Exception\UserException\NotFoundException;
use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Utility\UserSession;
use App\Exception\UserException\InvalidArgumentException;

class AuthenticationService
{
    protected UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function register(string $username, string $password, string $email): void
    {
        $encryptedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $user = new User();
        $user->setUsername($username)
            ->setPassword($encryptedPassword)
            ->setEmailAddress($email)
            ->setPrivilegeLevel(User::PRIVILEGE_LEVEL_USER);

        $this->repository->queue($user);
        $this->repository->save();
    }

    public function login(string $username, string $password): void
    {
        try {
            $user = $this->repository->getByUsername($username);
        } catch (NotFoundException $e) {
            // Username was not found
            throw InvalidArgumentException::incorrectLogin();
        }

        if (!password_verify($password, $user->getPassword())) {
            // Password is incorrect
            throw InvalidArgumentException::incorrectLogin();
        }

        UserSession::create(
            $user->getId(),
            $user->getUsername(),
            $user->getPrivilegeLevel()
        );
    }

    public function logout(): void
    {
        UserSession::destroy();
    }

    /**
     * Checks to see if the user sessionId is stored in the system cache. Gives a response `true` if the user session
     * cache file exists, meaning the user is logged in. False otherwise.
     *
     * @return bool
     */
    public function isUserLoggedIn(): bool
    {
        $session = UserSession::load();
        return !($session === null);
    }

    /**
     * Check if the current logged in user has a specific privilege level. Responds with `true` if the required level
     * is a match.
     *
     * @param int $requiredPrivilegeLevel
     * @return bool
     */
    public function userHasPrivilege(int $requiredPrivilegeLevel): bool
    {
        $session = UserSession::load();
        if ($session === null) {
            return false;
        }

        return ($session->getPrivilegeLevel() === $requiredPrivilegeLevel);
    }
}
