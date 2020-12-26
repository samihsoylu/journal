<?php declare(strict_types=1);

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
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNotTooShort($this->post, 'username', 4);
        $this->ensureValueIsNotTooLong($this->post, 'username', 64);
        $this->ensureEmailIsValid($this->post, 'email');
    }

    /**
     * @throws InvalidParameterException
     */
    public function login(): void
    {
        $requiredFields = ['username', 'password'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNotTooShort($this->post, 'username', 4);
        $this->ensureValueIsNotTooLong($this->post, 'username', 64);
    }
}
