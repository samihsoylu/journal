<?php

namespace App\Exception\UserException;

use App\Exception\UserException;

class ActionNotPermittedException extends UserException
{
    public static function invalidFormFieldProvided(string $fieldName, $code = 403): self
    {
        return new self("You have provided an invalid form field with the name '{$fieldName}'", $code);
    }
}