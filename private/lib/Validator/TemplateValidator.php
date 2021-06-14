<?php

namespace App\Validator;

class TemplateValidator extends AbstractValidator
{
    public function create(): void
    {
        $entryValidator = new EntryValidator($this->post);
        $entryValidator->create();
    }
}