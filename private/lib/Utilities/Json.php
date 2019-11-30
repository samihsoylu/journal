<?php

namespace App\Settings\Provider;

use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidJsonFileException;

/**
 * JSONFile class that deals with reading and writing json config files.
 */
class Json
{
    /**
     * @var string
     */
    private $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Reads from a json file
     *
     * @throws FileNotFoundException
     * @throws InvalidJsonFileException
     */
    public function read(): array
    {
        $filePath = $this->filePath;

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new FileNotFoundException("File '".$filePath."' could not be found, are you sure it exists?");
        }

        $fileData = file_get_contents($filePath);
        $jsonData = JSON_decode($fileData, true);

        if ($jsonData === null) {
            $fileName = explode('/', $filePath);
            $fileName = end($fileName);

            throw new InvalidJsonFileException("Failed to parse '{$fileName}' json file.");
        }

        return $jsonData;
    }

    /**
     * Writes to a json file
     *
     * @param array $data
     * @return bool
     */
    public function write(array $data): bool
    {
        $filePath = $this->filePath;

        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        if ($jsonData === false) {
            throw new \RuntimeException('Could not parse data: ' . print_r($data, true));
        }

        $write = file_put_contents($filePath, $jsonData);
        if ($write === false) {
            throw new \RuntimeException("Unable to write to file {$filePath}");
        }

        return true;
    }
}
