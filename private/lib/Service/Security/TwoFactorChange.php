<?php

declare(strict_types=1);

namespace App\Service\Security;

final readonly class TwoFactorChange
{
    /** @param list<string> $codes */
    public function __construct(
        public TwoFactorState $state,
        public array $codes,
        public string $fingerprint,
    ) {}
}
