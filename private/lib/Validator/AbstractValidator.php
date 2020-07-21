<?php

namespace App\Validator;

use App\Exception\UserException;
use App\Exception\UserException\InvalidParameterException;

abstract class AbstractValidator
{
    protected array $post;

    public function __construct()
    {
        $this->post = $_POST;
    }
    /**
     * Ensure that all $requireFields exist as a key in $this->params.
     *
     * @param array $requiredFields
     * @throws UserException
     */
    protected function ensureRequiredFieldsAreProvided(array $requiredFields): void
    {
        $fieldsFound = [];
        foreach ($this->post as $key => $value) {
            if (in_array($key, $requiredFields, true)) {
                $fieldsFound[] = $key;
            }
        }

        $missingFields = array_diff($requiredFields, $fieldsFound);
        $missingFieldsString = '';
        foreach ($missingFields as $missingField) {
            $missingFieldsString .= "{$missingField}, ";
        }

        if (count($missingFields) !== 0) {
            throw new UserException('You did not fill in all required fields: ' . rtrim($missingFieldsString, ','));
        }
    }

    protected function ensureValueIsNumeric(string $fieldName): void
    {
        if (!is_numeric($this->post[$fieldName])) {
            throw InvalidParameterException::notANumber($fieldName);
        }
    }

    protected function ensureValueIsString(string $fieldName): void
    {
        if (!is_string($this->post[$fieldName])) {
            throw InvalidParameterException::notAString($fieldName);
        }
    }

    protected function ensureValueIsNotTooShort(string $fieldName, int $minLength)
    {
        if (strlen($this->post[$fieldName]) < $minLength) {
            throw InvalidParameterException::stringTooShort($fieldName, $minLength);
        }
    }

    protected function ensureValueIsNotTooLong(string $fieldName, int $maxLength)
    {
        if(strlen($this->post[$fieldName]) > $maxLength) {
            throw InvalidParameterException::stringTooLong($fieldName, $maxLength);
        }
    }

    protected function ensureEmailIsValid(string $fieldName)
    {
        $email = $this->post[$fieldName];

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidParameterException::invalidEmail($fieldName);
        }
    }
}