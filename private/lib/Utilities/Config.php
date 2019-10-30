<?php


namespace App\Utilities;

use Exception;

/**
 * Class Config is responsible for generating constants at the intitial stage of this project.
 *
 * @package App\Utilities
 */
class Config
{
    /**
     * @var array holds all initialised json file paths
     */
    private static $isInitialised = [];

    /**
     * @var array holds all defined constants used for debugging purposes.
     */
    private static $availableConstants = [];

    /**
     * Initialises configuration file
     *
     * @param string $filePath
     * @throws Exception
     */
    public static function initialise(string $filePath): void
    {
        if (in_array($filePath, self::$isInitialised, true)) {
            throw new Exception("Configuration file '{$filePath}' was already initialised.");
        }

        // Load json file and generate constant
        $jsonData = JSONFile::read($filePath);
        self::setConstants($jsonData);

        // Keep track of initialised file
        self::$isInitialised[] = $filePath;
    }

    /**
     * This method generates constants dynamically for any given array.
     *
     * @param array $jsonData
     * @param string $index
     * @throws Exception
     */
    private static function setConstants(array $jsonData, string $index = ''): void
    {
        foreach ($jsonData as $key => $value) {

            $constantName = ($index !== '') ? "{$index}_{$key}" : $key;

            if (is_array($value)) {
                self::setConstants($value, $constantName);
                continue;
            }

            if (defined($constantName)) {
                throw new Exception("The constant {$constantName} is already defined! You have more than one configuration file with the same settings.");
            }
            define($constantName, $value);

            // Keeps track of which constant has been defined
            self::$availableConstants[$constantName] = $value;
        }
    }

    /**
     * Used for debugging purposes while development, returns all constants that have been defined using this class.
     *
     * @return array
     */
    public static function getAllConstants(): array
    {
        return self::$availableConstants;
    }

    /**
     * Used for debugging purposes while development, returns all file paths of configuration files that have been
     * initialised using this class.
     *
     * @return array
     */
    public static function getAllInitialisedConfigFiles(): array
    {
        return self::$isInitialised;
    }
}