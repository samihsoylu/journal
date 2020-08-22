<?php

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;

class AuthenticationValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function register(): void
    {
        $requiredFields = ['username', 'password', 'email'];
        $this->ensureFieldsAreNotMissing($requiredFields);

        $this->ensureValueIsNotTooShort('username', 4);
        $this->ensureValueIsNotTooLong('username', 64);
        $this->ensureEmailIsValid('email');
    }

    /**
     * @throws InvalidParameterException
     */
    public function login(): void
    {
        $requiredFields = ['username', 'password'];
        $this->ensureFieldsAreNotMissing($requiredFields);

        $this->ensureValueIsNotTooShort('username', 4);
        $this->ensureValueIsNotTooLong('username', 64);
    }
}