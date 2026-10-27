<?php

declare(strict_types=1);

namespace App\Service\Security;

interface TwoFactorStore
{
    public function find(int $userId) : ?TwoFactorState;

    /** @param list<string> $recoveryHashes */
    public function configure(int $userId, ?string $expectedRevision, ?string $encryptedSecret, ?int $counter, array $recoveryHashes, string $expectedFingerprint) : TwoFactorState;

    public function consumeCounter(int $userId, string $revision, int $counter) : bool;

    public function consumeRecovery(int $userId, string $revision, string $hash) : bool;
}
