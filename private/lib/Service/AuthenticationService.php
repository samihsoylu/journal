<?php declare(strict_types=1);

namespace App\Service;

use App\Database\Exception\NotFoundException;
use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Utility\Session;
use InvalidArgumentException;
use LengthException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;

class AuthenticationService
{
    protected const SESSION_ID = 'sessionId';
    protected const SESSION_DATA_USER_ID = 'sessionUserId';
    protected const SESSION_DATA_USER_NAME = 'sessionUsername';
    protected const SESSION_DATA_PRIVILEGE_LEVEL = 'sessionPrivilegeLevel';
    protected const DEFAULT_SESSION_EXPIRY_TIME = 86400; // 24 hours

    protected UserRepository $userRepository;

    protected CacheInterface $cache;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->cache          = new FilesystemAdapter('', 0, SESSION_CACHE_PATH);
    }

    public function registerNewUser(string $username, string $password, string $email): void
    {
        $this->validateUsernameAndPasswordFields($username, $password);

        // Ensure that the provided email address is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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

    public function loginUser(string $username, string $password): void
    {
        $this->validateUsernameAndPasswordFields($username, $password);

        try {
            $user = $this->userRepository->getByUsername($username);
        } catch (NotFoundException $e) {
            // user does not exist
            throw new InvalidArgumentException('Username does not exist');
        }

        if (!password_verify($password, $user->getPassword())) {
            // password is incorrect
            throw new InvalidArgumentException('Password is incorrect');
        }

        // Generate a random prefix
        $prefix = sha1(random_bytes(5));

        // Generate a unique session id
        $sessionId = uniqid($prefix, true);

        $sessionData = [
            self::SESSION_DATA_USER_ID         => $user->getId(),
            self::SESSION_DATA_USER_NAME       => $user->getUsername(),
            self::SESSION_DATA_PRIVILEGE_LEVEL => $user->getPrivilegeLevel(),
        ];

        // Get or create a cache item
        $item = $this->cache->getItem($sessionId);

        /** @var CacheItem $item */
        $item->set($sessionData);
        $item->expiresAfter(self::DEFAULT_SESSION_EXPIRY_TIME);
        $this->cache->save($item);

        Session::put(self::SESSION_ID, $sessionId);
    }

    public function logoutUser(): void
    {
        $sessionId = Session::get(self::SESSION_ID);

        /** @var CacheItem $cacheItem */
        $cacheItem = $this->cache->getItem($sessionId);
        if ($cacheItem->isHit()) {
            // deletes stored userId, username & privilegeLevel from cache
            $this->cache->delete($sessionId);
        }

        Session::destroy();
    }

    private function validateUsernameAndPasswordFields(string $username, string $password): void
    {
        // Ensure either of the fields are NOT empty
        if ($username === '' || $password === '') {
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
        $sessionId = Session::get(self::SESSION_ID);
        if (!$sessionId) {
            return false;
        }

        // Ensure session id is valid
        $cacheItem = $this->cache->getItem($sessionId);
        if (!$cacheItem->isHit()) {
            return false;
        }

        return true;
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
        $sessionId = Session::get(self::SESSION_ID);
        if (!$sessionId) {
            return false;
        }

        /** @var CacheItem $cacheItem */
        $cacheItem     = $this->cache->getItem($sessionId);
        $sessionData   = $cacheItem->get();
        $userPrivilege = $sessionData[self::SESSION_DATA_PRIVILEGE_LEVEL] ?? 0;

        return ($userPrivilege === $requiredPrivilegeLevel);
    }

    public function generateRandomString(): string
    {
        return md5(random_bytes(10));
    }
}