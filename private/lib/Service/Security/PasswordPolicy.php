<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Exception\UserException\InvalidArgumentException;

final class PasswordPolicy
{
    public static function validate(string $password) : void
    {
        if ( ! mb_check_encoding($password, 'UTF-8') || mb_strlen($password, 'UTF-8') < 12 || strlen($password) > 1024) {
            throw new InvalidArgumentException('New passwords must contain at least 12 characters and at most 1024 bytes.');
        }
    }
}
