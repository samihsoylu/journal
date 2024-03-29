<?php declare(strict_types=1);

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

    public function setCategoryOrder(): void
    {
        $fieldName = 'orderedCategoryIds';

        $requiredFields = [$fieldName];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $ids = $this->post[$fieldName] ?? null;
        $this->ensureValueIsArray($ids, $fieldName);
    }
}
