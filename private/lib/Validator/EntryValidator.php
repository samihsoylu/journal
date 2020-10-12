<?php

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;

class EntryValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function create(): void
    {
        $requiredFields = ['category_id', 'entry_title', 'entry_content'];
        $this->ensureRequiredFieldsAreProvided($requiredFields);

        $this->ensureValueIsNumeric('category_id');
        $this->ensureValueIsNotTooShort('entry_title', 4);
        $this->ensureValueIsNotTooShort('entry_content', 50);

        $this->ensureValueIsNotTooLong('entry_title', 128);
    }

    public function update(): void
    {
        $this->create();
    }
}