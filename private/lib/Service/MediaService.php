<?php declare(strict_types=1);

namespace App\Service;

use App\Service\Helper\MediaHelper;
use App\Service\ValueObject\Image;
use App\Utility\Encryptor;
use Defuse\Crypto\Key;

class MediaService
{
    public const UPLOAD_DIR = BASE_PATH . '/uploads';
    private Encryptor $encryptor;
    private MediaHelper $helper;

    public function __construct(Encryptor $encryptor, MediaHelper $helper)
    {
        $this->helper = $helper;
        $this->encryptor = $encryptor;
    }

    private function ensureUserUploadDir(int $userId)
    {
        $uploadDir = $this->helper->getUserUploadDir($userId);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    }

    public function encryptImage(int $userId, Image $image, Key $key, string $tmpPath): bool
    {
        $this->ensureUserUploadDir($userId);
        $targetPath = "{$this->helper->getUserUploadDir($userId)}/{$image->getName()}";

        $encryptedImage = $this->encryptor->encrypt((string) $image, $key);

        @unlink($tmpPath);

        return (bool) file_put_contents($targetPath, $encryptedImage);
    }

    public function getDecryptedImage(int $userId, string $imageName, Key $key): Image
    {
        $targetPath = "{$this->helper->getUserUploadDir($userId)}/{$imageName}";
        $image = @file_get_contents($targetPath);
        if (!$image) {
            throw new \RuntimeException('Could not find image.');
        }
        $decryptedImage = $this->encryptor->decrypt($image, $key);

        return Image::fromString($decryptedImage);
    }
}
