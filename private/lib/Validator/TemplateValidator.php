<?php

declare(strict_types=1);

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;

final class TemplateValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function create() : void
    {
        $requiredFields = ['category_id', 'template_title', 'entry_content'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNumeric($this->post, 'category_id');

        $this->ensureValueIsNotTooShort($this->post, 'template_title', 4);
        $this->ensureValueIsNotTooLong($this->post, 'template_title', 128);
        $this->ensureValueIsNotTooShort($this->post, 'entry_content', 1);

        if (array_key_exists('default_title_pattern', $this->post)) {
            $this->ensureValueIsNotTooShort($this->post, 'default_title_pattern', 0);
        }

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    /**
     * @throws InvalidParameterException
     */
    public function update() : void
    {
        $this->create();
    }

    /**
     * @throws InvalidParameterException
     */
    public function delete() : void
    {
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null);
    }

    public function getTemplateAsJsonView() : void
    {
        if (array_key_exists('entry_date_time', $this->get)) {
            $this->ensureValueIsNotTooLong($this->get, 'entry_date_time', 16);
        }
    }
}
