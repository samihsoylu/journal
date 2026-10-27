<?php

declare(strict_types=1);

namespace App\Utility\Export;

use App\Exception\UserException\InvalidArgumentException;

final class ExportFileName
{
    public static function prefix(string $username) : string
    {
        // Preserve distinct usernames while preventing paths/glob metacharacters.
        return preg_match('/\A[A-Za-z0-9._-]+\z/', $username) ? $username : '~' . bin2hex($username);
    }

    public static function suffix(string $fileName) : string
    {
        if ( ! preg_match('#\A[A-Za-z0-9._%~-]+__(\d{2}-\d{2}-\d{4}_\d{2}-\d{2}-\d{2}\.zip)\z#', $fileName, $matches)) {
            throw InvalidArgumentException::invalidFileNameProvided();
        }

        return $matches[1];
    }
}
