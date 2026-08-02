<?php

declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

final class InvalidArgumentException extends UserException
{
    public static function incorrectLogin(int $code = 406) : self
    {
        return new self('Username or Password is incorrect', $code);
    }

    public static function alreadyRegistered(string $fieldName, string $fieldValue, int $code = 406) : self
    {
        return new self(sprintf("User with %s '%s' already exists", $fieldName, $fieldValue), $code);
    }

    public static function categoryAlreadyExists(string $categoryName, int $code = 406) : self
    {
        return new self(sprintf("The category with name '%s' already exists", $categoryName), $code);
    }

    public static function templateAlreadyExists(string $templateTitle, int $code = 406) : self
    {
        return new self(sprintf("The template with title '%s' already exists", $templateTitle), $code);
    }

    public static function incorrectPassword(int $code = 406) : self
    {
        return new self('The password you provided is incorrect', $code);
    }

    public static function passwordsDoNotMatch(int $code = 406) : self
    {
        return new self('The two passwords provided do not match', $code);
    }

    public static function invalidFileNameProvided(int $code = 406) : self
    {
        return new self('Invalid file name provided', $code);
    }

    public static function invalidTimezoneProvided(string $timezone, int $code = 406) : self
    {
        return new self(sprintf('Timezone %s does not exist', $timezone), $code);
    }
}
