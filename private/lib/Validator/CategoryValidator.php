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
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key']);
    }

    /**
     * @throws InvalidParameterException
     */
    public function update(): void
    {
        $this->create();
    }

    /**
     * @throws InvalidParameterException
     */
    public function delete(): void
    {
        $this->ensureUserHasProvidedValidAntiCSRFToken($_GET['form_key']);
    }
}