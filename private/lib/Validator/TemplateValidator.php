<?php

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;

class TemplateValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function create(): void
    {
        $requiredFields = ['category_id', 'template_title', 'entry_content'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNumeric($this->post, 'category_id');

        $this->ensureValueIsNotTooShort($this->post, 'template_title', 4);
        $this->ensureValueIsNotTooLong($this->post, 'template_title', 128);
        $this->ensureValueIsNotTooShort($this->post, 'entry_content', 1);

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
