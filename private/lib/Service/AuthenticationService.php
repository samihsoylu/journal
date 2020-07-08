<?php declare(strict_types=1);

namespace App\Service;

use App\Database\Exception\NotFoundException;
use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Utility\UserSession;
use InvalidArgumentException;
use LengthException;

class AuthenticationService
{
    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function register(?string $username, ?string $password, ?string $email): void
    {
        $this->validateUsernameAndPasswordFields($username, $password);

        // Ensure that the provided email address is valid
        if ($email === null || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Provided email address is invalid');
        }

        $encryptedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $user = new User();
        $user->setUsername($username)
            ->setPassword($encryptedPassword)
            ->setEmailAddress($email)
            ->setPrivilegeLevel(User::PRIVILEGE_LEVEL_USER);

        $this->userRepository->queue($user);
        $this->userRepository->save();
    }

    public function login(?string $username, ?string $password): void
    {
        $this->validateUsernameAndPasswordFields($username, $password);

        try {
            $user = $this->userRepository->getByUsername($username);
        } catch (NotFoundException $e) {
            throw new InvalidArgumentException('Username does not exist');
        }

        if (!password_verify($password, $user->getPassword())) {
            throw new InvalidArgumentException('Password is incorrect');
        }

        $session = UserSession::create(
            $user->getId(),
            $user->getUsername(),
            $user->getPrivilegeLevel()
        );
        $session->save();
    }

    public function logout(): void
    {
        UserSession::destroy();
    }

    /**
     * Simple validation for user provided username and password.
     *
     * @param string|null $username
     * @param string|null $password
     */
    private function validateUsernameAndPasswordFields(?string $username, ?string $password): void
    {
        // Ensure either of the fields are NOT empty
        if ($username === null || $password === null) {
            throw new InvalidArgumentException('Username or Password was not provided', 406);
        }

        // Make sure that the username length is at least 4 characters long
        $minUsernameLength = 4;
        if (strlen($username) < $minUsernameLength) {
            throw new LengthException("The username must be at least {$minUsernameLength} characters in length", 406);
        }
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