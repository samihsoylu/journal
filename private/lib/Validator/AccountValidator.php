<?php

namespace App\Validator;

use App\Exception\UserException\ActionNotPermittedException;
use App\Exception\UserException\InvalidArgumentException;

class AccountValidator extends AbstractValidator
{
    public function changePassword(): void
    {
        $requiredFields = ['currentPassword', 'newPassword', 'confirmPassword'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensurePasswordsMatch($this->post['newPassword'], $this->post['confirmPassword']);
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key']);
    }

    public function changeEmail(): void
    {
        $this->ensureRequiredFieldsAreProvided($this->post, ['email']);

        $this->ensureEmailIsValid($this->post, 'email');

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key']);
    }

    public function deleteAccount(): void
    {
        $this->ensureRequiredFieldsAreProvided($this->post, ['password']);

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key']);
    }

    public function updateWidgets(): void
    {
        $allowedFields = [
            'quickAddEntriesBoxEntriesOverview',
            'form_key',
        ];

        foreach ($this->post as $fieldName => $fieldValue) {
            if (!in_array($fieldName, $allowedFields, true)) {
                throw ActionNotPermittedException::invalidFormFieldProvided($fieldName);
            }
        }

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key']);
    }

    private function ensurePasswordsMatch(string $newPassword, string $confirmPassword): void
    {
        if ($newPassword !== $confirmPassword) {
            throw InvalidArgumentException::passwordsDoNotMatch();
        }
    }
}
