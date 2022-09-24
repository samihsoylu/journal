<?php declare(strict_types=1);

namespace App\Service\Helper;

use App\Service\MediaService;

class MediaHelper
{
    public function removeUserUploadDir(int $userId): void
    {
        $uploadDirectory = $this->getUserUploadDir($userId);

        $allImages = $this->getAllImageNamesForUser($userId);
        foreach ($allImages as $image) {
            @unlink("{$uploadDirectory}/{$image}");
        }

        @rmdir($uploadDirectory);
    }

    public function getAllImageNamesForUser(int $userId)
    {
        return array_values(array_diff(scandir($this->getUserUploadDir($userId)), ['..', '.']));
    }

    public function getUserUploadDir(int $userId)
    {
        return MediaService::UPLOAD_DIR . "/{$userId}";
    }
}
