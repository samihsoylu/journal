<?php

declare(strict_types=1);

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;

final class CategoryValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function create() : void
    {
        $requiredFields = ['category_name', 'category_description'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);
        $this->ensureValueIsNotTooShort($this->post, 'category_name', 1);
        $this->ensureValueIsNotTooShort($this->post, 'category_description', 0);

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

    public function setCategoryOrder() : void
    {
        // Sorting may send several requests from the same open page.
        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key'] ?? null, false);
        $fieldName = 'orderedCategoryIds';

        $requiredFields = [$fieldName];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $ids = $this->post[$fieldName] ?? null;
        $this->ensureValueIsArray($ids, $fieldName);
    }
}
