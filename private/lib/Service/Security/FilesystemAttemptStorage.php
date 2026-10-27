<?php

declare(strict_types=1);

namespace App\Service\Security;

use RuntimeException;
use Symfony\Component\RateLimiter\LimiterStateInterface;
use Symfony\Component\RateLimiter\Policy\Window;
use Symfony\Component\RateLimiter\Storage\StorageInterface;
use Throwable;

// Used under the limiter's shared lock. Storage errors must never grant guesses.
final readonly class FilesystemAttemptStorage implements StorageInterface
{
    public function __construct(private string $directory)
    {
        if ( ! is_dir($directory) && ! @mkdir($directory, 0o700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Could not create authentication attempt storage');
        }
    }

    private function path(string $identity) : string
    {
        clearstatcache();

        if ( ! is_dir($this->directory) || ! is_readable($this->directory) || ! is_writable($this->directory) || ! is_executable($this->directory)) {
            throw new RuntimeException('Authentication attempt storage is unavailable');
        }

        return $this->directory . '/' . hash('sha256', $identity) . '.state';
    }

    public function save(LimiterStateInterface $limiterState) : void
    {
        $path = $this->path($limiterState->getId());
        $lifetime = $limiterState->getExpirationTime();
        $payload = serialize([$lifetime === null ? null : microtime(true) + $lifetime, $limiterState]);
        $temporary = @tempnam($this->directory, '.attempt-');

        if ($temporary === false) {
            throw new RuntimeException('Could not persist authentication attempts');
        }

        try {
            if ( ! @chmod($temporary, 0o600) || @file_put_contents($temporary, $payload) !== strlen($payload) || ! @rename($temporary, $path)) {
                throw new RuntimeException('Could not persist authentication attempts');
            }
        } finally {
            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    }

    public function fetch(string $limiterStateId) : ?LimiterStateInterface
    {
        $path = $this->path($limiterStateId);

        if ( ! file_exists($path)) {
            return null;
        }
        $payload = @file_get_contents($path);

        if ($payload === false || strlen($payload) > 65536) {
            throw new RuntimeException('Could not read authentication attempts');
        }

        try {
            // This application configures only Symfony's fixed-window policy.
            $data = @unserialize($payload, ['allowed_classes' => [Window::class]]);

            if ( ! is_array($data) || count($data) !== 2 || ! $data[1] instanceof Window || $data[1]->getId() !== $limiterStateId
                || ($data[0] !== null && ! is_float($data[0]))) {
                throw new RuntimeException('Invalid authentication attempt state');
            }

            return $data[0] !== null && $data[0] <= microtime(true) ? null : $data[1];
        } catch (Throwable) {
            throw new RuntimeException('Could not read authentication attempts');
        }
    }

    public function delete(string $limiterStateId) : void
    {
        $path = $this->path($limiterStateId);

        if (file_exists($path) && ! @unlink($path)) {
            throw new RuntimeException('Could not reset authentication attempts');
        }
    }
}
