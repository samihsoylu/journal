<?php

namespace App\Validator;

use App\Exception\UserException\InvalidParameterException;

class EntryValidator extends AbstractValidator
{
    /**
     * @throws InvalidParameterException
     */
    public function index(): void
    {
        $this->ensureValueIsNumeric($this->get, 'categoryId');
        $this->ensureValueIsNumeric($this->get, 'entries_limit');

        $this->ensureDateFormatIsValid($this->get, 'date_from');
        $this->ensureDateFormatIsValid($this->get, 'date_to');
    }

    /**
     * @throws InvalidParameterException
     */
    public function create(): void
    {
        $requiredFields = ['category_id', 'entry_title', 'entry_content'];
        $this->ensureRequiredFieldsAreProvided($this->post, $requiredFields);

        $this->ensureValueIsNumeric($this->post, 'category_id');
        $this->ensureValueIsNotTooShort($this->post, 'entry_title', 4);
        $this->ensureValueIsNotTooShort($this->post, 'entry_content', 50);

        $this->ensureValueIsNotTooLong($this->post, 'entry_title', 128);
    }

    public function update(): void
    {
        $this->create();
    }

    private function ensureDateFormatIsValid(array $values, string $fieldName): void
    {
        if (isset($values[$fieldName])) {
            $date = \DateTime::createFromFormat('M d, Y', $values[$fieldName]);

            if ($date === false) {
                throw InvalidParameterException::invalidDateFormat($fieldName);
            }
        }
    }
}