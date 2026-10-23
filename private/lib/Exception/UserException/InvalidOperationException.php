<?php

declare(strict_types=1);

namespace App\Exception\UserException;

use App\Exception\UserException;

final class InvalidOperationException extends UserException
{
    public static function userIsNotLoggedIn() : self
    {
        return new self('Something went wrong.. It appears you are not logged in.');
    }

    public static function loginAttemptsExceeded(int $loginCount) : self
    {
        return new self(sprintf('You have %d failed login attempts, you have been blocked from logging in for 1 hour', $loginCount));
    }

    public static function insufficientPrivileges(string $userPrivilegeLevelAsString) : self
    {
        return new self(sprintf('Your privilege level is %s which is not enough to perform this operation', $userPrivilegeLevelAsString));
    }

    public static function actionIsAlreadyRunning(string $actionDescription) : self
    {
        return new self(sprintf('An action for %s is already running', $actionDescription));
    }
}
