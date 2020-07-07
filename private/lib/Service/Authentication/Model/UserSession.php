<?php

namespace App\Service\Authentication\Model;

use App\Utility\Cache;
use App\Utility\Session;
use Symfony\Component\Cache\CacheItem;

/**
 * Class UserSession is a representation of a single user browsing the website. It stores session information of each
 * individual visitor and it handles the information by reading/writing to the local cache.
 *
 * @package App\Service\Authentication\Model
 */
class UserSession
{
    protected const DEFAULT_SESSION_EXPIRY_TIME = 86400; // 24 hours

    /**
     * @var string represents the id of the session stored in the visitors browser
     */
    protected string $sessionId;

    /**
     * @var int represents the logged in user's id
     */
    protected int $userId;

    /**
     * @var string represents the logged in user's username
     */
    protected string $username;

    /**
     * @var int represents the logged in user's privilege level
     */
    protected int $privilegeLevel;

    protected const SESSION_ID = 'SessionID';
    protected const USER_ID    = 'UserID';
    protected const USER_NAME  = 'Username';
    protected const USER_PRIVILEGE_LEVEL = 'PrivlegeLevel';

    public function __construct(string $id, int $userId, string $username, int $privilegeLevel)
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

    protected static function fromStruct(array $struct): self
    {
        return new self(
            $struct[self::SESSION_ID],
            $struct[self::USER_ID],
            $struct[self::USER_NAME],
            $struct[self::USER_PRIVILEGE_LEVEL]
        );
    }

    protected function toStruct(): array
    {
        return [
            self::SESSION_ID           => $this->sessionId,
            self::USER_ID              => $this->userId,
            self::USER_NAME            => $this->username,
            self::USER_PRIVILEGE_LEVEL => $this->privilegeLevel
        ];
    }

    public function save(): void
    {
        $cache = Cache::getInstance();

        // Get or create a cache item
        $item = $cache->getItem($this->sessionId);

        /** @var CacheItem $item */
        $item->set($this->toStruct());
        $item->expiresAfter(self::DEFAULT_SESSION_EXPIRY_TIME);
        $cache->save($item);

        Session::put(self::SESSION_ID, $this->sessionId);
    }

    public static function load(): ?self
    {
        $sessionId = Session::get(self::SESSION_ID);
        if ($sessionId === null) {
            return null;
        }

        $cache = Cache::getInstance();

        /** @var CacheItem $item */
        $item = $cache->getItem($sessionId);
        if (!$item->isHit()) {
            // Cache item has expired, user is no longer considered to be logged in
            return null;
        }

        return self::fromStruct($item->get());
    }

    public static function destroy(): void
    {
        $sessionId = Session::get(self::SESSION_ID);
        if ($sessionId === null) {
            return;
        }

        $cache     = Cache::getInstance();
        $cacheItem = $cache->getItem($sessionId);
        if ($cacheItem->isHit()) {
            // deletes stored userId, username & privilegeLevel from cache
            $cache->delete($sessionId);
        }

        Session::destroy();
    }
}