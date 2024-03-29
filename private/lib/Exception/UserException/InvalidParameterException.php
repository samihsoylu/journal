<?php declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

class InvalidParameterException extends UserException
{
    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function notNumeric(string $fieldName, int $code = 406): self
    {
        return new self("The provided field '{$fieldName}' is not a number", $code);
    }

    public static function stringTooLong(string $fieldName, int $maxChars, int $code = 406): self
    {
        return new self("The provided field '{$fieldName}' can be '{$maxChars}' characters long maximum", $code);
    }

    public static function stringTooShort(string $fieldName, int $minChars, int $code = 406): self
    {
        return new self("The provided field '{$fieldName}' must be at least '{$minChars}' characters long", $code);
    }

    public static function invalidFieldValue(string $fieldName, int $code = 406): self
    {
        return new self("The provided value in field {$fieldName} is invalid", $code);
    }

    public static function missingField(string $fieldName, int $code = 406): self
    {
        return new self("Field {$fieldName} is required", $code);
    }

    public static function invalidDateFormat(string $fieldName, int $code = 406): self
    {
        return new self("Field {$fieldName} provided an incorrect date format", $code);
    }

    public static function invalidFormKey(): self
    {
        return new self("Invalid Form Key. Please try again.");
    }

    public static function notArray(string $fieldName): self
    {
        return new self("Field {$fieldName} must be an array", 406);
    }
}
