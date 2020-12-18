<?php

namespace App\Validator;

use App\Database\Model\User;
use App\Exception\UserException\InvalidParameterException;

class UserValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function create(): void
    {
        $requiredFields = ['username', 'password', 'email', 'form_key'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNotTooShort($this->post, 'username', 4);
        $this->ensureValueIsNotTooLong($this->post, 'username', 64);
        $this->ensureEmailIsValid($this->post, 'email');

        $this->ensureValueIsNumeric($this->post,'privilegeLevel');
        $this->ensureProvidedPrivilegeLevelExists($this->post['privilegeLevel'], 'privilegeLevel');

        $this->ensureUserHasProvidedValidAntiCSRFToken($_POST['form_key']);
    }

    private function ensureProvidedPrivilegeLevelExists(int $privilegeLevel, string $fieldName): void
    {
        if (!array_key_exists($privilegeLevel, User::ALLOWED_PRIVILEGE_LEVELS)) {
            throw InvalidParameterException::invalidFieldValue($fieldName);
        }
    }
}