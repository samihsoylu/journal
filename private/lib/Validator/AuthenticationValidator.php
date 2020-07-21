<?php

namespace App\Validator;

class AuthenticationValidator extends AbstractValidator
{
    public function register(): void
    {
        $requiredFields = ['username', 'password', 'email'];
        $this->ensureRequiredFieldsAreProvided($requiredFields);

        $this->ensureValueIsNotTooShort('username', 4);
        $this->ensureValueIsNotTooLong('username', 64);
        $this->ensureEmailIsValid('email');
    }

    public function login(): void
    {
        $requiredFields = ['username', 'password'];
        $this->ensureRequiredFieldsAreProvided($requiredFields);

        $this->ensureValueIsNotTooShort('username', 4);
        $this->ensureValueIsNotTooLong('username', 64);
    }
}