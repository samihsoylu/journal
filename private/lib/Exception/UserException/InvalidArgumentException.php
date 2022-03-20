<?php declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

class InvalidArgumentException extends UserException
{
    public function __construct(string $message = "", int $code = 0)
    {
        parent::__construct($message, $code);
    }

    public static function incorrectLogin(int $code = 406): self
    {
        return new self("Username or Password is incorrect", $code);
    }

    public static function alreadyRegistered(string $fieldName, string $fieldValue, int $code = 406): self
    {
        return new self("User with {$fieldName} '{$fieldValue}' already exists", $code);
    }

    public static function categoryAlreadyExists(string $categoryName, int $code = 406): self
    {
        return new self("The category with name '{$categoryName}' already exists", $code);
    }

    public static function templateAlreadyExists(string $templateTitle, int $code = 406): self
    {
        return new self("The template with title '{$templateTitle}' already exists", $code);
    }

    public static function incorrectPassword(int $code = 406): self
    {
        return new self("The password you provided is incorrect", $code);
    }

    public static function passwordsDoNotMatch(int $code = 406): self
    {
        return new self("The two passwords provided do not match", $code);
    }

    public static function invalidFileNameProvided(int $code = 406)
    {
        return new self("Invalid file name provided", $code);
    }
}
