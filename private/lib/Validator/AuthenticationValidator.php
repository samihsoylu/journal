<?php declare(strict_types=1);

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;
use App\Utility\Session;

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
        $requiredFields = ['username', 'password', 'form_key'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $token = $this->post['form_key'];
        if (hash_equals(Session::get('login_form_key'), $token) === false) {
            throw InvalidParameterException::invalidFormKey();
        }

        $this->ensureValueIsNotTooShort($this->post, 'username', 4);
        $this->ensureValueIsNotTooLong($this->post, 'username', 64);
    }
}
