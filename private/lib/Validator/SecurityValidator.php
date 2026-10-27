<?php

declare(strict_types=1);

namespace App\Validator;

final class SecurityValidator extends AbstractValidator
{
    public function begin() : void
    {
        $this->cancel();
        $this->password();
    }

    public function confirm() : void
    {
        $this->cancel();
        $this->ensureRequiredFieldsAreProvided($this->post, ['code']);
        $this->ensureValueIsNotTooShort($this->post, 'code', 1);
        $this->ensureValueIsNotTooLong($this->post, 'code', 128);
    }

    public function disable() : void
    {
        $this->confirm();
        $this->password();
    }

    public function regenerate() : void
    {
        $this->disable();
    }

    public function cancel() : void
    {
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    private function password() : void
    {
        $this->ensureRequiredFieldsAreProvided($this->post, ['password']);
        $this->ensureValueIsNotTooShort($this->post, 'password', 0);
        $this->ensureValueIsNotTooLong($this->post, 'password', 1024);
    }
}
