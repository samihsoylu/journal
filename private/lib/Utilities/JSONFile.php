<?php

namespace App\Utilities;

use Exception;

/**
 * JSONFile class that initialises data associated with
 * the project environment.
 *
 * @package App
 * @author Samih Soylu <mail@samihsoylu.nl>
 */
class JSONFile {

    /**
     * @var array holds all initialised json file paths
     */
    private static $isInitialised = [];

    /**
     * @var array holds all json data read from the file path.
     */
    private static $jsonData = [];

    /**
     * Initialises configuration file
     *
     * @param string $filePath
     * @throws Exception
     */
    public static function initialise(string $filePath): void
    {
        if (in_array($filePath, self::$isInitialised, true)) {
            throw new Exception("Configuration file has already been initialised. {$filePath}");
        }

        self::readJSONFile($filePath);
        self::setConstants();
        self::$isInitialised[] = $filePath;
    }

    private static function readJSONFile(string $filePath): void
    {
        $jsonString = file_get_contents($filePath);
        self::$jsonData = json_decode($jsonString, true);
    }

    /**
     * This method generates constants for any given array. The purpose of this method is to
     * generate constants dynamically. If there are any changes in the provided json then
     * this is automatically reflected in this method.
     *
     * @param string $index
     * @throws Exception
     */
    private static function setConstants(string $index = ''): void
    {
        foreach(self::$jsonData as $key => $value) {

            $constantName = ($index !== '') ? "{$index}_{$key}" : $key;

            if (is_array($value)) {
                self::setConstants($constantName);
                continue;
            }

            if (defined($constantName)) {
                throw new Exception("The constant {$constantName} is already defined!");
            }
            define($constantName, $value);
        }
    }
}


