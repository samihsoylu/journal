<?php

namespace App\Utilities;

use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidJsonFileException;
use \RuntimeException;

/**
 * JSONFile class that deals with reading and writing json files.
 */
class JSONFile
{

    /**
     * @throws FileNotFoundException
     * @throws InvalidJsonFileException
     */
    public static function read(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new FileNotFoundException("File '{$filePath}' could not be opened, make sure the file exists.");
        }

        $fileData = file_get_contents($filePath);
        $jsonData = json_decode($fileData, true);

        if ($jsonData === null) {
            throw new InvalidJsonFileException("Failed to decode: '{$filePath}' json file.");
        }

        return $jsonData;
    }
    
    /**
     * @throws RuntimeException
     */
    public static function write(string $filePath, array $data): bool
    {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        if ($jsonData === false) {
            throw new RuntimeException('Could not json encode data: ' . print_r($data, true));
        }

        $write = file_put_contents($filePath, $jsonData);
        if ($write === false) {
            throw new RuntimeException("Unable to write to file {$filePath}");
        }

        return true;
    }
}
