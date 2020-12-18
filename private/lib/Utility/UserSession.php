<?php declare(strict_types=1);

namespace App\Utility;

use Symfony\Component\Cache\CacheItem;

/**
 * Class UserSession is a utility and a representation of a single user browsing the website. It stores session information of each
 * individual visitor and it handles the information by reading/writing to the local cache.
 */
class UserSession
{
    protected string $sessionId;
    protected int    $userId;
    protected string $username;
    protected int    $privilegeLevel;

    protected string $antiCSRFToken;
    protected string $encodedEncryptionKey;

    protected const SESSION_ID = 'SessionID';
    protected const USER_ID    = 'UserID';
    protected const USER_NAME  = 'Username';
    protected const USER_PRIVILEGE_LEVEL = 'PrivilegeLevel';
    protected const ANTI_CSRF_TOKEN        = 'AntiCSRFToken';
    protected const ENCODED_ENCRYPTION_KEY = 'EncodedEncryptionKey';

    private function __construct(string $id, int $userId, string $username, int $privilegeLevel)
    {
        $this->sessionId      = $id;
        $this->userId         = $userId;
        $this->username       = $username;
        $this->privilegeLevel = $privilegeLevel;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPrivilegeLevel(): int
    {
        return $this->privilegeLevel;
    }

    public function setPrivilegeLevel(int $privilegeLevel): void
    {
        $this->privilegeLevel = $privilegeLevel;
    }

    public function getAntiCSRFToken(): string
    {
        return $this->antiCSRFToken;
    }

    public function setAntiCSRFToken(string $antiCSRFToken): void
    {
        $this->antiCSRFToken = $antiCSRFToken;
    }

    public function getEncodedEncryptionKey(): string
    {
        return $this->encodedEncryptionKey;
    }

    public function setEncodedEncryptionKey(string $encodedEncryptionKey): void
    {
        $this->encodedEncryptionKey = $encodedEncryptionKey;
    }

    private static function fromStruct(array $struct): self
    {
        return new self(
            $struct[self::SESSION_ID],
            $struct[self::USER_ID],
            $struct[self::USER_NAME],
            $struct[self::USER_PRIVILEGE_LEVEL]
        );
    }

    private function toStruct(): array
    {
        return [
            self::SESSION_ID           => $this->sessionId,
            self::USER_ID              => $this->userId,
            self::USER_NAME            => $this->username,
            self::USER_PRIVILEGE_LEVEL => $this->privilegeLevel,
        ];
    }

    private function generateNewAntiCSRFToken(): void
    {
        $prefix = sha1(random_bytes(5));

        $this->antiCSRFToken = sha1($prefix . $this->sessionId);
    }

    public function regenerateNewAntiCSRFToken(): void
    {
        $this->generateNewAntiCSRFToken();
        $this->save();
    }

    public static function create(int $userId, string $username, int $privilegeLevel, string $encodedEncryptionKey): self
    {
        // Generate a random prefix
        $prefix = sha1(random_bytes(5));

        // Generate a unique session id
        $sessionId = uniqid($prefix, true);

        $self = new self(
            $sessionId,
            $userId,
            $username,
            $privilegeLevel
        );

        $self->generateNewAntiCSRFToken();
        $self->setEncodedEncryptionKey($encodedEncryptionKey);
        $self->save();

        return $self;
    }

    /**
     * Saves this session in to the users browser and local cache
     *
     * @return void
     */
    public function save(): void
    {
        $cache = Cache::getInstance();

        // Get or create a cache item
        $item = $cache->getItem($this->sessionId);

        /** @var CacheItem $item */
        $item->set($this->toStruct());
        $item->expiresAfter(DEFAULT_SESSION_EXPIRY_TIME);
        $cache->save($item);

        Session::put(self::SESSION_ID, $this->sessionId);
        Session::put(self::ANTI_CSRF_TOKEN, $this->antiCSRFToken);
        self::setCookie(self::ENCODED_ENCRYPTION_KEY, $this->encodedEncryptionKey, time() + DEFAULT_SESSION_EXPIRY_TIME);
    }

    /**
     * Reads the user $_SESSION and returns an UserSession instance if it exists in the cache. Returns null if user is
     * not logged in.
     *
     * @return self|null
     */
    public static function load(): ?self
    {
        $sessionId = Session::get(self::SESSION_ID);
        $encodedEncryptionKey = $_COOKIE[self::ENCODED_ENCRYPTION_KEY] ?? null;

        if (!$sessionId || !$encodedEncryptionKey) {
            return null;
        }

        /** @var CacheItem $item */
        $cache = Cache::getInstance();
        $item = $cache->getItem($sessionId);
        if (!$item->isHit()) {
            // Cache item has expired, user is no longer considered to be logged in
            return null;
        }

        $userSession = self::fromStruct($item->get());
        $token = Session::get(self::ANTI_CSRF_TOKEN);

        $userSession->setAntiCSRFToken($token);
        $userSession->setEncodedEncryptionKey($encodedEncryptionKey);

        return $userSession;
    }

    /**
     * Deletes an existing user session by clearing the cache and user $_SESSION
     *
     * @return void
     */
    public static function destroy(): void
    {
        $sessionId = Session::get(self::SESSION_ID);
        if ($sessionId === null) {
            return;
        }

        $cache     = Cache::getInstance();
        $cacheItem = $cache->getItem($sessionId);
        if ($cacheItem->isHit()) {
            $cache->delete($sessionId);
        }

        unset($_COOKIE[self::ENCODED_ENCRYPTION_KEY]);
        self::setCookie(self::ENCODED_ENCRYPTION_KEY, '', time() - 3600);

        Session::destroy();
    }

    private static function setCookie(string $cookieName, string $cookieValue, int $cookieExpiryTime): void
    {
        \setcookie($cookieName, $cookieValue, $cookieExpiryTime, '/');
    }
}
