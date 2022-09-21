<?php declare(strict_types=1);

namespace App\Utility\Lock;

class LockName
{
    public const ACTION_EXPORT_ALL_ENTRIES_FOR_USER = 'export_all_entries_for_user';

    public static function create(int $userId, string $username, string $action): string
    {
        return "{$userId}_{$username}_{$action}";
    }
}
