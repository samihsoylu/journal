<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Exception\UserException\InvalidOperationException;
use LogicException;
use RuntimeException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final readonly class AttemptLimiter
{
    private array $factories;

    public function __construct(string $directory = SESSION_CACHE_PATH . '/attempts')
    {
        if ( ! is_dir($directory) && ! @mkdir($directory, 0o700, true) && ! is_dir($directory)) {
            throw new RuntimeException('Could not create authentication attempt storage');
        }
        $storage = new FilesystemAttemptStorage($directory . '/state');
        // Explicit locks make reservation atomic across separate PHP sessions.
        $locks = new LockFactory(new FlockStore($directory . '/locks'));
        $factories = [];

        foreach (['login', 'two-factor-peer', 'two-factor-account'] as $purpose) {
            $factories[$purpose] = new RateLimiterFactory(['id' => $purpose, 'policy' => 'fixed_window', 'limit' => 10, 'interval' => '1 hour'], $storage, $locks);
        }
        $this->factories = $factories;
    }

    public function reserve(string $purpose, string $identity) : void
    {
        $limiter = $this->factory($purpose)->create(hash('sha256', $identity));

        if ( ! $limiter->consume(1)->isAccepted()) {
            throw InvalidOperationException::loginAttemptsExceeded(10);
        }
    }

    public function reset(string $purpose, string $identity) : void
    {
        $this->factory($purpose)->create(hash('sha256', $identity))->reset();
    }

    private function factory(string $purpose) : RateLimiterFactory
    {
        return $this->factories[$purpose] ?? throw new LogicException('Unknown authentication attempt purpose');
    }
}
