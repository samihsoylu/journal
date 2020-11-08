<?php

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
}