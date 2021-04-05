<?php declare(strict_types=1);

namespace App\Validator;

use App\Exception\UserException\InvalidOperationException;
use App\Exception\UserException\InvalidParameterException;

class EntryValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function index(): void
    {
        $this->ensureOptionalValueIsNumeric($this->get, 'category_id');
        $this->ensureOptionalValueIsNumeric($this->get, 'page_size');
        $this->ensureOptionalValueIsNumeric($this->get, 'page');

        $this->ensureOptionalDateFormatIsValid($this->get, 'date_from');
        $this->ensureOptionalDateFormatIsValid($this->get, 'date_to');
    }

    /**
     * @throws InvalidParameterException|InvalidOperationException
     */
    public function create(): void
    {
        $requiredFields = ['category_id', 'entry_title', 'entry_content'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNumeric($this->post, 'category_id');
        $this->ensureValueIsNotTooShort($this->post, 'entry_title', 4);
        $this->ensureValueIsNotTooShort($this->post, 'entry_content', 1);

        $this->ensureValueIsNotTooLong($this->post, 'entry_title', 128);

        $this->ensureUserHasProvidedValidAntiCSRFToken($this->post['form_key']);
    }

    public function update(): void
    {
        $this->create();
    }

    public function delete(): void
    {
        $this->ensureUserHasProvidedValidAntiCSRFToken($_GET['form_key']);
    }

    private function ensureOptionalDateFormatIsValid(array $values, string $fieldName): void
    {
        $value = $values[$fieldName] ?? null;
        if ($value !== null && $value !== '') {
            $date = \DateTime::createFromFormat('M d, Y', trim($value));

            if ($date === false) {
                throw InvalidParameterException::invalidDateFormat($fieldName);
            }
        }
    }
}
