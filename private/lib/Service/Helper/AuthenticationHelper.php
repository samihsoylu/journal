<?php

declare(strict_types=1);

namespace App\Service\Helper;

use App\Service\Security\AttemptLimiter;

final readonly class AuthenticationHelper
{
    public function __construct(private AttemptLimiter $attempts) {}

    public function reserveLoginAttempt(string $identity) : void
    {
        $this->attempts->reserve('login', $this->peerIdentity($identity));
    }

    public function resetLoginAttempts(string $identity) : void
    {
        $this->attempts->reset('login', $this->peerIdentity($identity));
    }

    private function peerIdentity(string $identity) : string
    {
        // Resolve account identity before calling this helper. Never trust a
        // caller-supplied forwarding header or reset another account's budget.
        return $identity . "\0" . ($_SERVER['REMOTE_ADDR'] ?? '');
    }
}
