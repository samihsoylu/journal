<?php

namespace App\Utilities;

use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidJsonFileException;

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
        if (!file_exists($filePath)) {
            throw new FileNotFoundException("File '".$filePath."' could not be found, please make sure the file exists.");
        }

        $fileData = file_get_contents($filePath);
        $jsonData  = JSON_decode($fileData, true);

        if ($jsonData === null) {
            $fileName = explode('/', $filePath);
            $fileName = end($fileName);

            throw new InvalidJsonFileException("Failed to decode: '{$fileName}' json file.");
        }

        return $jsonData;
    }
}
