<?php

declare(strict_types=1);

namespace App\Service\Helper;

use App\Service\MediaService;

final class MediaHelper
{
    public function removeUserUploadDir(int $userId) : void
    {
        $uploadDirectory = $this->getUserUploadDir($userId);

        $allImages = $this->getAllImageNamesForUser($userId);

        foreach ($allImages as $image) {
            @unlink(sprintf('%s/%s', $uploadDirectory, $image));
        }

        @rmdir($uploadDirectory);
    }

    public function getAllImageNamesForUser(int $userId) : array
    {
        $directory = $this->getUserUploadDir($userId);

        if ( ! is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        return array_values(array_diff(scandir($directory), ['..', '.']));
    }

    public function getUserUploadDir(int $userId) : string
    {
        return MediaService::UPLOAD_DIR . ('/' . $userId);
    }
}
