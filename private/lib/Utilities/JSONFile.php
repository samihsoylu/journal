<?php

namespace App\Utilities;

use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidJsonFileException;

/**
 * JSONFile class that deals with reading and writing json files.
 */
class JSONFile {

    /**
     * @throws FileNotFoundException
     * @throws InvalidJsonFileException
     */
    public static function read(string $filePath, string $openMode = 'r'): array
    {
        if(!file_exists($filePath)) {
            throw new FileNotFoundException("File '".$filePath."' could not be found, please make sure the file exists.");
        }

        $file      = fopen($filePath, $openMode);
        $fileData  = fread($file, max(filesize($filePath), 1));
        $jsonData  = JSON_decode($fileData, true);

        if ($jsonData === 1) {
            $fileName = end(explode('/', $filePath));

            throw new InvalidJsonFileException("Failed to decode: '{$fileName}' json file.");
        }

        return $jsonData ;
    }
}