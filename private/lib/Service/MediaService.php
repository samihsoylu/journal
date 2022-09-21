<?php

namespace App\Service;

use App\Service\ValueObject\Image;
use App\Utility\Encryptor;
use Defuse\Crypto\Key;

class MediaService
{
    public const UPLOAD_DIR = BASE_PATH . '/uploads';

    public Encryptor  $encryptor;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    private function ensureUserUploadDir(int $userId)
    {
        if (!file_exists(self::UPLOAD_DIR . "/{$userId}")) {
            mkdir(self::UPLOAD_DIR . "/{$userId}", 0777, true);
        }
    }

    public function getUserUploadDir(int $userId)
    {
        return self::UPLOAD_DIR . "/{$userId}";
    }

    public function getAllImageNamesForUser(int $userId)
    {
        return array_values(array_diff(scandir($this->getUserUploadDir($userId)), ['..', '.']));
    }

    public function encryptImage(int $userId, Image $image, Key $key, string $tmpPath): bool
    {
        $this->ensureUserUploadDir($userId);
        $targetPath = "{$this->getUserUploadDir($userId)}/{$image->getName()}";

        $encryptedImage = $this->encryptor->encrypt((string) $image, $key);

        @unlink($tmpPath);

        return file_put_contents($targetPath, $encryptedImage);
    }

    public function getDecryptedImage(int $userId, string $imageName, Key $key): Image
    {
        $targetPath = "{$this->getUserUploadDir($userId)}/{$imageName}";
        $image = @file_get_contents($targetPath);
        if (!$image) {
            throw new \RuntimeException('Could not find image.');
        }
        $decryptedImage = $this->encryptor->decrypt($image, $key);

        return Image::fromString($decryptedImage);
    }
}
