<?php declare(strict_types=1);

namespace App\Utility;

/**
 * Class Sanitize holds methods that help sanitizing user input.
 */
class Sanitize
{
    public const OPTION_LOWERCASE = 'lowercase';
    public const OPTION_TRIM = 'trim';
    public const OPTION_STRIP = 'strip';
    public const OPTION_CLEAN_SPACES = 'clean-spaces';
    public const OPTION_CLEAN_SPECIAL_CHARS = 'clean-special-chars';

    public const TYPE_INT = 'int';
    public const TYPE_STRING = 'string';

    private const OPTIONS_ALLOWED = [
        self::OPTION_LOWERCASE,
        self::OPTION_TRIM,
        self::OPTION_STRIP,
        self::OPTION_CLEAN_SPACES,
        self::OPTION_CLEAN_SPECIAL_CHARS,
    ];

    /**
     * Provided value is sanitized based on the order of given options:
     * - trim eliminates spaces that may exist at the start or end of the string.
     * - capitalize makes the initial character of a string upper case and the rest lower case.
     * - lowercase makes the entire string lower case
     * - htmlspecialchars removes all html tags from the string
     * - strip combines lowercase, trim and htmlspecialchars at once.
     *
     * @param string $value
     * @param string[] $options
     * @return string
     */
    public static function string(string $value, array $options = [self::OPTION_STRIP]): string
    {
        foreach ($options as $option) {
            if (!in_array($option, self::OPTIONS_ALLOWED, true)) {
                throw new \LogicException("Option '{$option}' does not exist");
            }

            switch ($option) {
                case self::OPTION_LOWERCASE:
                    $value = strtolower($value);
                    break;
                case self::OPTION_TRIM:
                    $value = trim($value);
                    break;
                case self::OPTION_STRIP:
                    $value = trim($value);
                    $value = htmlspecialchars($value);
                    break;
                case self::OPTION_CLEAN_SPACES:
                    $value = str_replace(' ', '-', $value);
                    break;
                case self::OPTION_CLEAN_SPECIAL_CHARS:
                    $value = preg_replace('/[^A-Za-z0-9\-]/', '', $value);
                    break;
            }
        }

        return $value;
    }

    public static function int(string $value): int
    {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * User provided fields through $_GET can be sanitized using this method. This method allows optional variables
     * to be sanitized too by returning null if the provided field name does not exist.
     *
     * @param array $getVariable
     * @param string $fieldName
     * @param string $expectedDataType int|string
     * @param array $options must be provided when using dataType 'string'
     *
     * @return int|string|null
     * @see Sanitize::string() for possible options for the $options parameter
     */
    public static function getVariable(array $getVariable, string $fieldName, string $expectedDataType, array $options = [self::OPTION_STRIP])
    {
        $value = $getVariable[$fieldName] ?? null;

        if ($value !== null && $value !== '') {
            switch ($expectedDataType) {
                case self::TYPE_INT:
                    return self::int($value);
                case self::TYPE_STRING:
                    return self::string($value, $options);
            }
        }

        return null;
    }
}
