<?php


namespace App\Utilities;

use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidConfigFileException;

/**
 * This class is responsible for generating constants based on a json output from the configuration files in
 * /private/conf/
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
     * @throws FileNotFoundException
     * @throws InvalidConfigFileException
     * @throws \App\Exceptions\InvalidJsonFileException
     */
    public static function initialise(string $filePath): void
    {
        if (in_array($filePath, self::$isInitialised, true)) {
            throw new InvalidConfigFileException("Configuration file '{$filePath}' was already initialised.");
        }

        // Load json file and generate constant
        $jsonData = JSONFile::read($filePath);
        self::setConstants($jsonData);

        // Keep track of initialised file
        self::$isInitialised[] = $filePath;
    }

    /**
     * @throws InvalidConfigFileException
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
                throw new InvalidConfigFileException("The constant {$constantName} is already defined! You have more than one configuration file with the same settings.");
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

    /**
     * @throws InvalidConfigFileException
     */
    public static function ensureConstantsAreDefined(array $requiredConstants): void
    {
        foreach ($requiredConstants as $constant) {
            if (!defined($constant)) {
                throw new InvalidConfigFileException("The required constant '{$constant}' is undefined!");
            }
        }
    }
}