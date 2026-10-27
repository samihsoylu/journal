<?php

declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

final class InvalidParameterException extends UserException
{
    public static function notNumeric(string $fieldName, int $code = 406) : self
    {
        return new self(sprintf("The provided field '%s' is not a number", $fieldName), $code);
    }

    public static function stringTooLong(string $fieldName, int $maxChars, int $code = 406) : self
    {
        return new self(sprintf("The provided field '%s' can be '%d' characters long maximum", $fieldName, $maxChars), $code);
    }

    public static function stringTooShort(string $fieldName, int $minChars, int $code = 406) : self
    {
        return new self(sprintf("The provided field '%s' must be at least '%d' characters long", $fieldName, $minChars), $code);
    }

    public static function invalidFieldValue(string $fieldName, int $code = 406) : self
    {
        return new self(sprintf('The provided value in field %s is invalid', $fieldName), $code);
    }

    public static function missingField(string $fieldName, int $code = 406) : self
    {
        return new self(sprintf('Field %s is required', $fieldName), $code);
    }

    public static function invalidDateFormat(string $fieldName, int $code = 406) : self
    {
        return new self(sprintf('Field %s provided an incorrect date format', $fieldName), $code);
    }

    public static function invalidFormKey() : self
    {
        return new self('Invalid Form Key. Please try again.');
    }

    public static function notArray(string $fieldName) : self
    {
        return new self(sprintf('Field %s must be an array', $fieldName), 406);
    }
}
