<?php

namespace App\Utilities;

use Exception;

/**
 * JSONFile class that deals with reading and writing json files.
 *
 * @author Samih Soylu <mail@samihsoylu.nl>
 */
class JSONFile {

    /**
     * @param string $filePath
     * @param string $openMode
     * @return array
     * @throws Exception
     */
    public static function read(string $filePath, $openMode = 'r'): array
    {
        if(!file_exists($filePath)) {
            throw new Exception("File '".$filePath."' in parameter does not exist, please make sure the file exists.");
        }

        $file      = fopen($filePath, $openMode);
        $fileData  = fread($file, max(filesize($filePath), 1));
        $jsonData  = JSON_decode($fileData, true);

        return $jsonData;
    }
}