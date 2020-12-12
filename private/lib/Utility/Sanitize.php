<?php

namespace App\Utility;

/**
 * Class Sanitize holds methods that help sanitizing user input.
 */
class Sanitize
{
    /**
     * Provided value is sanitized based on the order of given options:
     * - trim eliminates spaces that may exist at the start or end of the string.
     * - capitalize makes the initial character of a string upper case and the rest lower case.
     * - lowercase makes the entire string lower case
     * - htmlspecialchars removes all html tags from the string
     * - strip combines lowercase, trim and htmlspecialchars at once.
     *
     * @param string $value
     * @param string $options Sanitizing options separate with '|'. Options: trim, capitalize, lowercase, htmlspecialchars
     * @return string
     */
    public static function string(string $value, string $options): string
    {
        $optionList = explode('|', $options);
        if ($optionList === false) {
            return $value;
        }

        foreach ($optionList as $option) {
            switch ($option) {
                case 'lowercase':
                    $value = strtolower($value);
                    break;
                case 'capitalize':
                    $value = ucfirst($value);
                    break;
                case 'trim':
                    $value = trim($value);
                    break;
                case 'htmlspecialchars':
                    $value = htmlspecialchars($value);
                    break;
                case 'strip':
                    $value = strtolower($value);
                    $value = trim($value);
                    $value = htmlspecialchars($value);
                    break;
            }
        }

        return $value;
    }

    public static function int(string $value): int
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * User provided fields through $_GET can be sanitized using this method. This method allows optional variables
     * to be sanitized too by returning null if the provided field name does not exist.
     *
     * @see Sanitize::string() for possible options for the $options parameter
     *
     * @param array $getVariable
     * @param string $fieldName
     * @param string $dataType int|string
     * @param string|null $options must be provided when using dataType 'string'
     *
     * @return int|string|null
     */
    public static function getVariable(array $getVariable, string $fieldName, string $dataType, string $options = null)
    {
        $value = $getVariable[$fieldName] ?? null;

        if ($dataType === 'string' && $options === null) {
            throw new \RuntimeException('The options parameter cannot be null when you provide string data types');
        }

        if ($value !== null && $value !== '') {
            switch ($dataType) {
                case 'int':
                    return self::int($value);
                case 'string':
                    return self::string($value, $options);
            }
        }

        return null;
    }
}