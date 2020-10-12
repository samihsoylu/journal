<?php

namespace App\Validator;

use App\Exception\UserException;
use App\Exception\UserException\InvalidParameterException;

abstract class AbstractValidator
{
    protected array $post;

    public function __construct(array $postData)
    {
        $this->post = $postData;
    }

    /**
     * A fancy methods that runs the provided method name in this class.
     *
     * @param string $method
     * @return void
     */
    public function validate(string $method): void
    {
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }

    /**
     * Ensure that all $requireFields exist as a key in $this->params.
     *
     * @param array $requiredFields
     * @throws InvalidParameterException
     */
    protected function ensureRequiredFieldsAreProvided(array $requiredFields): void
    {
        $fieldsFound = [];
        foreach ($this->post as $key => $value) {
            // Map all existing fields in post to $fieldsFound based on $requiredFields
            if (in_array($key, $requiredFields, true)) {
                $fieldsFound[] = $key;
            }
        }

        // Find fields that are missing
        $missingFields = array_diff($requiredFields, $fieldsFound);

        // Convert missing fields to a string
        $missingFieldsString = '';
        foreach ($missingFields as $missingField) {
            $missingFieldsString .= "{$missingField}, ";
        }
        $missingFieldsString = rtrim($missingFieldsString, ', ');

        if (count($missingFields) !== 0) {
            throw InvalidParameterException::missingField($missingFieldsString);
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
            throw InvalidParameterException::invalidFieldValue($fieldName);
        }
    }
}