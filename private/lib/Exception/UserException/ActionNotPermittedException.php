<?php

declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

final class ActionNotPermittedException extends UserException
{
    public static function invalidFormFieldProvided(string $fieldName, $code = 403) : self
    {
        return new self(sprintf("You have provided an invalid form field with the name '%s'", $fieldName), $code);
    }
}
