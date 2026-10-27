<?php

declare(strict_types=1);

namespace App\Validator;

final class MediaValidator extends AbstractValidator
{
    public function upload() : void
    {
        // Multiple editor images can upload while the entry form stays open.
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null, false);
    }
}
