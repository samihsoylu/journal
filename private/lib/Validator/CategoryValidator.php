<?php

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;

class CategoryValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function create(): void
    {
        $requiredFields = ['category_name', 'category_description'];
        $this->ensureRequiredFieldsAreProvided($requiredFields);
    }

    /**
     * @throws InvalidParameterException
     */
    public function update(): void
    {
        $requiredFields = ['category_name', 'category_description'];
        $this->ensureRequiredFieldsAreProvided($requiredFields);
    }
}