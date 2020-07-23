<?php declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

class InvalidParameterException extends UserException
{
    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function notANumber(string $fieldName, int $code = 406): self
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

    public static function invalidEmail(string $fieldName, int $code = 406): self
    {
        return new self ("The provided value in field {$fieldName} is invalid");
    }
}
