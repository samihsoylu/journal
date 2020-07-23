<?php

namespace App\Utility;

/**
 * Class Sanitizer holds methods that help sanitizing certain elements within the project.
 */
class Sanitizer
{
    /**
     * Cleans up a given string based on the provided options. The provided value is sanitized based on the order of
     * given options.
     *
     * @param string $value
     * @param string $options sanitizing options separated by '|'
     * @return string
     */
    public static function sanitizeString(string $value, string $options): string
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
            }
        }

        return $value;
    }
}