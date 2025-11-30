<?php

declare(strict_types=1);

namespace App\Utility;

use Symfony\Component\Cache\CacheItem;

/**
 * Class UserSession is a utility and a representation of a single user browsing the website. It stores session information of each
 * individual visitor and it handles the information by reading/writing to the local cache.
 */
final class UserSession
{
    private string $antiCSRFToken;
    private string $encodedEncryptionKey;
    private ?string $sessionId = null;
    private ?int $userId = null;
    private ?string $username = null;
    private ?int $privilegeLevel = null;
    private ?string $timezone = null;

    private const string SESSION_ID = 'SessionID';
    private const string USER_ID = 'UserID';
    private const string USER_NAME = 'Username';
    private const string USER_PRIVILEGE_LEVEL = 'PrivilegeLevel';
    private const string ANTI_CSRF_TOKEN = 'AntiCSRFToken';
    private const string ENCODED_ENCRYPTION_KEY = 'EEK';
    private const string TIMEZONE = 'timezone';

    private bool $isLoaded = false;

    public function __construct(private readonly Cache $cache)
    {
        $this->autoLoad();
    }

    /**
     * Automatically loads the session when UserSession is instantiated.
     * This eliminates the need for explicit load() calls throughout the codebase.
     */
    private function autoLoad() : void
    {
        $sessionId = Session::get(self::SESSION_ID);
        $encodedEncryptionKey = $_COOKIE[self::ENCODED_ENCRYPTION_KEY] ?? null;

        if ( ! $sessionId || ! $encodedEncryptionKey) {
            $this->isLoaded = false;

            return;
        }

        /** @var CacheItem $item */
        $item = $this->cache->getItem($sessionId);

        if ( ! $item->isHit()) {
            // Cache item has expired, user is no longer considered to be logged in
            $this->isLoaded = false;

            return;
        }

        $this->fromStruct($item->get());
        $token = Session::get(self::ANTI_CSRF_TOKEN);

        $this->setAntiCSRFToken($token);
        $this->setEncodedEncryptionKey($encodedEncryptionKey);

        $this->isLoaded = true;
    }

    public function exists() : bool
    {
        return $this->isLoaded;
    }

    public function getSessionId() : ?string
    {
        return $this->sessionId;
    }

    private function setSessionId(string $sessionId) : void
    {
        $this->sessionId = $sessionId;
    }

    public function getUserId() : ?int
    {
        return $this->userId;
    }

    private function setUserId(int $userId) : void
    {
        $this->userId = $userId;
    }

    public function getUsername() : ?string
    {
        return $this->username;
    }

    private function setUsername(string $username) : void
    {
        $this->username = $username;
    }

    public function getPrivilegeLevel() : ?int
    {
        return $this->privilegeLevel;
    }

    private function setPrivilegeLevel(int $privilegeLevel) : void
    {
        $this->privilegeLevel = $privilegeLevel;
    }

    public function getAntiCSRFToken() : string
    {
        return $this->antiCSRFToken;
    }

    public function setAntiCSRFToken(string $antiCSRFToken) : void
    {
        $this->antiCSRFToken = $antiCSRFToken;
    }

    public function getEncodedEncryptionKey() : string
    {
        return $this->encodedEncryptionKey;
    }

    public function setEncodedEncryptionKey(string $encodedEncryptionKey) : void
    {
        $this->encodedEncryptionKey = $encodedEncryptionKey;
    }

    public function getTimezone() : ?string
    {
        return $this->timezone;
    }

    public function setTimezone(?string $timezone) : void
    {
        $this->timezone = $timezone;
    }

    private function fromStruct(array $struct) : void
    {
        $this->setSessionId($struct[self::SESSION_ID]);
        $this->setUserId($struct[self::USER_ID]);
        $this->setUsername($struct[self::USER_NAME]);
        $this->setPrivilegeLevel($struct[self::USER_PRIVILEGE_LEVEL]);
        $this->setTimezone($struct[self::TIMEZONE] ?? null);
    }

    private function toStruct() : array
    {
        return [
            self::SESSION_ID => $this->sessionId,
            self::USER_ID => $this->userId,
            self::USER_NAME => $this->username,
            self::USER_PRIVILEGE_LEVEL => $this->privilegeLevel,
            self::TIMEZONE => $this->timezone,
        ];
    }

    public static function generateNewAntiCSRFToken(string $sessionId) : string
    {
        $prefix = sha1(random_bytes(5));

        return sha1($prefix . $sessionId);
    }

    public function regenerateNewAntiCSRFToken() : void
    {
        $this->antiCSRFToken = self::generateNewAntiCSRFToken($this->sessionId);
        $this->save();
    }

    public function create(
        int $userId,
        string $username,
        int $privilegeLevel,
        string $encodedEncryptionKey,
        ?string $timezone,
    ) : void {
        // Generate a cryptographically secure random session ID
        $sessionId = bin2hex(random_bytes(32));

        $this->setSessionId($sessionId);
        $this->setUserId($userId);
        $this->setUsername($username);
        $this->setPrivilegeLevel($privilegeLevel);
        $this->setTimezone($timezone);

        $this->antiCSRFToken = self::generateNewAntiCSRFToken($sessionId);
        $this->setEncodedEncryptionKey($encodedEncryptionKey);
        $this->save();
    }

    /**
     * Saves this session in to the users browser and local cache.
     */
    public function save() : void
    {
        // Get or create a cache item
        $item = $this->cache->getItem($this->sessionId);

        /** @var CacheItem $item */
        $item->set($this->toStruct());
        $item->expiresAfter(DEFAULT_SESSION_EXPIRY_TIME);

        $this->cache->save($item);

        Session::put(self::SESSION_ID, $this->sessionId);
        Session::put(self::ANTI_CSRF_TOKEN, $this->antiCSRFToken);
        $this->setCookie(self::ENCODED_ENCRYPTION_KEY, $this->encodedEncryptionKey, time() + DEFAULT_SESSION_EXPIRY_TIME);
    }

    /**
     * Deletes the current user session by clearing the cache and user $_SESSION.
     */
    public function destroy() : void
    {
        $sessionId = Session::get(self::SESSION_ID);

        if ($sessionId === null) {
            return;
        }

        $cacheItem = $this->cache->getItem($sessionId);

        if ($cacheItem->isHit()) {
            $this->cache->delete($sessionId);
        }

        unset($_COOKIE[self::ENCODED_ENCRYPTION_KEY]);
        $this->setCookie(self::ENCODED_ENCRYPTION_KEY, '', time() - 3600);

        Session::destroy();
    }

    private function setCookie(string $cookieName, string $cookieValue, int $cookieExpiryTime) : void
    {
        $options = [
            'expires' => $cookieExpiryTime,
            'path' => '/',
            'httponly' => true,  // Prevents JavaScript access (XSS protection)
            'samesite' => 'Strict',  // Prevents CSRF attacks
        ];

        // Only set Secure flag when SSL is enabled (production environments)
        if (SSL_IS_ENABLED) {
            $options['secure'] = true;  // Only transmit over HTTPS
        }

        setcookie($cookieName, $cookieValue, $options);
    }
}
