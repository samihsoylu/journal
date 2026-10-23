<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Database\Model\User;

final class CredentialFingerprint
{
    public static function from(User $user, ?string $revision) : string
    {
        return self::withRevision(self::credentials($user->getPassword(), $user->getEncryptionKey(), $user->getPrivilegeLevel()), $revision);
    }

    public static function credentials(string $passwordHash, string $protectedKey, int $privilege) : string
    {
        return hash('sha256', $passwordHash . "\0" . $protectedKey . "\0" . $privilege);
    }

    public static function withRevision(string $credentials, ?string $revision) : string
    {
        return hash('sha256', $credentials . "\0" . ($revision ?? 'never-enrolled'));
    }
}
