<?php

declare(strict_types=1);

namespace App\Service\Security;

use Defuse\Crypto\Key;

// Account-key operations, separate from content encryption and storage.
interface UserKeys
{
    public function generateProtectedKey(string $password) : string;

    public function getEncodedKeyFromProtectedKey(string $protectedKey, string $password) : string;

    public function getKeyFromProtectedKey(string $protectedKey, string $password) : Key;

    public function getKeyFromEncodedKey(string $encodedKey) : Key;

    public function changePassword(string $protectedKey, $currentPassword, $newPassword) : string;
}
