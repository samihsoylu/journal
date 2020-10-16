<?php

namespace App\Validator;

use App\Exception\UserException;
use App\Exception\UserException\InvalidParameterException;

abstract class AbstractValidator
{
    protected array $post;

    protected array $get;

    public function __construct(array $postData, array $getData = [])
    {
        $this->post = $postData;
        $this->get  = $getData;
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
     * Ensure that all $requireFields exist
     *
     * @param array $array
     * @param array $requiredFields
     * @throws InvalidParameterException
     */
    protected function ensureRequiredFieldsAreProvided(array $array, array $requiredFields): void
    {
        $missingFields = [];
        foreach ($requiredFields as $fieldName) {
            if (!array_key_exists($fieldName, $array)) {
                $missingFields[] = $fieldName;
            }
        }

        if (count($missingFields) > 0) {
            $missingFields = implode(', ', $missingFields);
            throw InvalidParameterException::missingField($missingFields);
        }
    }

    protected function ensureValueIsNumeric(array $values, string $fieldName): void
    {
        if (!is_numeric($values[$fieldName])) {
            throw InvalidParameterException::notNumeric($fieldName);
        }
    }

    protected function ensureOptionalValueIsNumeric(array $values, string $fieldName): void
    {
        $value = $values[$fieldName] ?? null;

        if ($value !== null && $value !== '') {
            $this->ensureValueIsNumeric($values, $fieldName);
        }
    }

    protected function ensureValueIsNotTooShort(array $values, string $fieldName, int $minLength): void
    {
        if (strlen($values[$fieldName]) < $minLength) {
            throw InvalidParameterException::stringTooShort($fieldName, $minLength);
        }
    }

    protected function ensureValueIsNotTooLong(array $values, string $fieldName, int $maxLength): void
    {
        if(strlen($values[$fieldName]) > $maxLength) {
            throw InvalidParameterException::stringTooLong($fieldName, $maxLength);
        }
    }

    protected function ensureEmailIsValid(array $values, string $fieldName): void
    {
        if ($values[$fieldName] === '' || !filter_var($values[$fieldName], FILTER_VALIDATE_EMAIL)) {
            throw InvalidParameterException::invalidFieldValue($fieldName);
        }
    }
}