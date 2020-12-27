<?php declare(strict_types=1);

namespace App\Validator;

use App\Database\Model\User;
use App\Exception\UserException\InvalidOperationException;
use App\Exception\UserException\InvalidParameterException;

class UserValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException|InvalidOperationException
     */
    public function create(): void
    {
        $requiredFields = ['username', 'password', 'email', 'privilegeLevel', 'form_key'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNotTooShort($this->post, 'username', 4);
        $this->ensureValueIsNotTooLong($this->post, 'username', 64);
        $this->ensureEmailIsValid($this->post, 'email');

        $privilegeLevel = $this->ensureValueIsNumeric($this->post, 'privilegeLevel');
        $this->ensureProvidedPrivilegeLevelExists($privilegeLevel, 'privilegeLevel');

        $this->ensureUserHasProvidedValidAntiCSRFToken($_POST['form_key']);
    }

    public function delete(): void
    {
        $this->ensureUserHasProvidedValidAntiCSRFToken($_GET['form_key']);
    }

    private function ensureProvidedPrivilegeLevelExists(int $privilegeLevel, string $fieldName): void
    {
        if (!array_key_exists($privilegeLevel, User::ALLOWED_PRIVILEGE_LEVELS)) {
            throw InvalidParameterException::invalidFieldValue($fieldName);
        }
    }
}
