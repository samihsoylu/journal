<?php

declare(strict_types=1);

namespace App\Validator;

use App\Exception\UserException\ActionNotPermittedException;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidParameterException;
use DateTimeZone;

final class AccountValidator extends AbstractValidator
{
    public function setDateTimeZone() : void
    {
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
        $timezone = $this->post['timezone'] ?? null;

        if ( ! is_string($timezone) || ! in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            throw InvalidParameterException::invalidFieldValue('timezone');
        }
    }

    public function changePassword() : void
    {
        $requiredFields = ['currentPassword', 'newPassword', 'confirmPassword'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);
        $this->ensureValueIsNotTooShort($this->post, 'currentPassword', 0);

        $this->ensurePasswordsMatch($this->post['newPassword'], $this->post['confirmPassword']);
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    public function changeEmail() : void
    {
        $this->ensureRequiredFieldsAreProvided($this->post, ['email']);

        $this->ensureEmailIsValid($this->post, 'email');

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    public function deleteAccount() : void
    {
        $this->ensureRequiredFieldsAreProvided($this->post, ['password']);
        $this->ensureValueIsNotTooShort($this->post, 'password', 0);

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    public function updateWidgets() : void
    {
        $allowedFields = [
            'quickAddEntriesBoxEntriesOverview',
            'form_key',
        ];

        foreach (array_keys($this->post) as $fieldName) {
            if ( ! in_array($fieldName, $allowedFields, true)) {
                throw ActionNotPermittedException::invalidFormFieldProvided($fieldName);
            }
        }

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    private function ensurePasswordsMatch(mixed $newPassword, mixed $confirmPassword) : void
    {
        if ( ! is_string($newPassword) || ! is_string($confirmPassword)) {
            throw InvalidParameterException::invalidFieldValue('newPassword');
        }

        if ($newPassword !== $confirmPassword) {
            throw InvalidArgumentException::passwordsDoNotMatch();
        }
    }

    public function exportEntries() : void
    {
        $this->ensureRequiredFieldsAreProvided($this->post, ['form_key']);
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    public function deleteEntryExport() : void
    {
        $this->ensureRequiredFieldsAreProvided($this->post, ['form_key', 'fileName']);
        $this->ensureValueIsNotTooShort($this->post, 'fileName', 1);
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }
}
