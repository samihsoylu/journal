<?php declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

class InvalidOperationException extends UserException
{
    public static function userIsNotLoggedIn(): self
    {
        return new self("Something went wrong.. It appears you are not logged in.");
    }

    public static function loginAttemptsExceeded(int $loginCount): self
    {
        return new self("You have {$loginCount} failed login attempts, you have been blocked from logging in for 1 hour");
    }

    public static function insufficientPrivileges(string $userPrivilegeLevelAsString): self
    {
        return new self("Your privilege level is {$userPrivilegeLevelAsString} which is not enough to perform this operation");
    }

    public static function actionIsAlreadyRunning(string $actionDescription): self
    {
        return new self("An action for {$actionDescription} is already running");
    }
}
