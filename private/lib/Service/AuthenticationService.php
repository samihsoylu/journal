<?php declare(strict_types=1);

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Utility\UserSession;
use App\Exception\UserException\InvalidArgumentException;
use Doctrine\ORM\ORMException;

class AuthenticationService
{
    protected UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
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
        $user = $this->repository->getByUsername($username);
        if ($user === null || !password_verify($password, $user->getPassword())) {
            // Username or password is incorrect
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
