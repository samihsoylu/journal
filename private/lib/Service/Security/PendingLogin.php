<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Exception\UserException\InvalidParameterException;
use App\Utility\PrivateCookie;
use App\Utility\Session;
use Psr\Clock\ClockInterface;
use RuntimeException;

final readonly class PendingLogin
{
    public const string COOKIE = 'JournalPendingKey';
    private const string SESSION = 'pending_login';

    public function __construct(private ClockInterface $clock) {}

    public function begin(int $userId, string $fingerprint, string $encodedKey) : void
    {
        $this->clear();

        if (session_status() === PHP_SESSION_ACTIVE && ! session_regenerate_id(true)) {
            throw new RuntimeException('Could not regenerate the login session');
        }
        $expires = $this->clock->now()->getTimestamp() + 300;
        Session::put(self::SESSION, ['userId' => $userId, 'fingerprint' => $fingerprint, 'digest' => hash('sha256', $encodedKey), 'expires' => $expires, 'csrf' => bin2hex(random_bytes(32))]);
        PrivateCookie::write(self::COOKIE, $encodedKey, $expires);
    }

    public function state() : ?array
    {
        $state = Session::get(self::SESSION);
        $key = $_COOKIE[self::COOKIE] ?? null;

        if ( ! is_array($state) || ! is_int($state['userId'] ?? null) || ! is_int($state['expires'] ?? null)
            || ! is_string($state['fingerprint'] ?? null) || ! is_string($state['digest'] ?? null) || ! is_string($state['csrf'] ?? null)
            || $state['expires'] <= $this->clock->now()->getTimestamp() || ! is_string($key) || $key === '' || strlen($key) > 1024
            || ! hash_equals($state['digest'], hash('sha256', $key))) {
            $this->clear();

            return null;
        }

        return $state;
    }

    public function requireState(mixed $csrf) : array
    {
        $state = $this->state();

        if ($state === null || ! is_string($csrf) || ! hash_equals($state['csrf'], $csrf)) {
            throw InvalidParameterException::invalidFormKey();
        }

        return $state;
    }

    public function clear() : void
    {
        Session::delete(self::SESSION);
        unset($_COOKIE[self::COOKIE]);
        PrivateCookie::write(self::COOKIE, '', $this->clock->now()->getTimestamp() - 3600);
    }
}
