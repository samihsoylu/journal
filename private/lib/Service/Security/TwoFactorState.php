<?php

declare(strict_types=1);

namespace App\Service\Security;

final readonly class TwoFactorState
{
    public function __construct(
        public int $userId,
        public string $revision,
        public ?string $encryptedSecret,
        public ?int $lastCounter,
    ) {}

    public function enabled() : bool
    {
        return $this->encryptedSecret !== null;
    }
}
