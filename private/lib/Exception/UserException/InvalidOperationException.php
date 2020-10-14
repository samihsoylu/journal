<?php

namespace App\Exception\UserException;

use App\Exception\UserException;

class InvalidOperationException extends UserException
{
    public static function userIsNotLoggedIn(): self
    {
        return new self("Something went wrong.. It appears you are not logged in.");
    }
}