<?php

namespace App\Validator;

class CategoryValidator extends AbstractValidator
{
    public function create(): void
    {
        $requiredFields = ['category_name', 'category_description'];
        $this->ensureRequiredFieldsAreProvided($requiredFields);
    }

    public function update(): void
    {
        $requiredFields = ['category_name', 'category_description'];
        $this->ensureRequiredFieldsAreProvided($requiredFields);
    }
}