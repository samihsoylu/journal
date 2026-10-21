<?php

declare(strict_types=1);

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;
use App\Utility\Session;

final class AuthenticationValidator extends AbstractValidator
{
    public function logout() : void
    {
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    /**
     * @throws InvalidParameterException
     */
    public function register() : void
    {
        $requiredFields = ['username', 'password', 'email'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNotTooShort($this->post, 'username', 4);
        $this->ensureValueIsNotTooLong($this->post, 'username', 64);
        $this->ensureValueIsNotTooShort($this->post, 'password', 1);
        $this->ensureValueIsNotTooLong($this->post, 'password', 1024);
        $this->ensureEmailIsValid($this->post, 'email');
    }

    /**
     * @throws InvalidParameterException
     */
    public function login() : void
    {
        $requiredFields = ['username', 'password', 'form_key'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $token = $this->post['form_key'];
        $sessionToken = Session::get('login_form_key');

        if ( ! is_string($sessionToken) || $sessionToken === '' || ! is_string($token) || ! hash_equals($sessionToken, $token)) {
            throw InvalidParameterException::invalidFormKey();
        }

        $this->ensureValueIsNotTooShort($this->post, 'username', 4);
        $this->ensureValueIsNotTooLong($this->post, 'username', 64);
        // Keep legacy passwords valid while rejecting malformed HTTP values.
        $this->ensureValueIsNotTooShort($this->post, 'password', 0);
    }
}
