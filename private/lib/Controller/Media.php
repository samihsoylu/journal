<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthenticationService;
use App\Service\MediaService;
use App\Service\ValueObject\Image;
use Exception;

final class Media extends AbstractController
{
    public const string MEDIA_URL = BASE_URL . '/media';
    public const string MEDIA_UPLOAD_POST_URL = self::MEDIA_URL . '/upload';
    public const string MEDIA_GET_URL = self::MEDIA_URL . '/{imageName}';

    public function __construct(
        AuthenticationService $authenticationService,
        public MediaService $service,
    ) {
        parent::__construct($authenticationService);

        $this->redirectLoggedOutUsersToLoginPage();
    }

    public function upload() : void
    {
        $file = $_FILES['file'] ?? null;
        $name = $file['name'];
        $tmpName = $file['tmp_name'];

        $imageType = strtolower(pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        $file['type'] = $imageType;

        $binary = $this->getUploadedImageBinaryAsBase64Encoded($file);

        $hashedImageName = sha1($name . uniqid());
        $image = new Image($binary, $hashedImageName, $imageType);

        $uploaded = $this->service->encryptImage(
            $this->getUserId(),
            $image,
            $this->getUserEncryptionKey(),
            $tmpName,
        );

        if ( ! $uploaded) {
            $this->renderCouldNotUploadFile();
        }

        $this->renderJsonResponse(['location' => self::MEDIA_URL . ('/' . $hashedImageName)]);
    }

    public function showImage() : void
    {
        $imageName = $this->getRouteParameters()['imageName'] ?? '';

        try {
            $image = $this->service->getDecryptedImage(
                $this->getUserId(),
                $imageName,
                $this->getUserEncryptionKey(),
            );
        } catch (Exception) {
            $this->renderNotFound();
        }

        header('Cache-Control: max-age=86400');
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));
        header('Content-Type: image/' . $image->getType());
        echo $image->getBinary();
    }

    private function renderNotFound() : never
    {
        header('HTTP/1.1 404 Not Found');
        $this->renderTemplate('errors/404');
        exit;
    }

    private function getUploadedImageBinaryAsBase64Encoded(?array $file) : string
    {
        $this->ensureIsUploadRequest($file);
        $this->ensureFileWasUploaded($file['tmp_name']);
        $this->ensureFileTypeIsValid($file['type']);
        $this->ensureFileSizeIsWithinLimits($file['size']);

        $image = file_get_contents($file['tmp_name']);

        if ( ! $image) {
            header('HTTP/1.1 500 Server Error');
            $this->renderJsonResponse(['Could not read uploaded file.']);
            exit;
        }

        return base64_encode($image);
    }

    private function ensureFileSizeIsWithinLimits(int $size) : void
    {
        // Convert MB to Bytes
        $sizeLimit = 1024 * 1024 * IMAGE_UPLOAD_SIZE_LIMIT;

        if ($size > $sizeLimit) {
            header('HTTP/1.1 400 File size too large.');
            $this->renderJsonResponse(['File size is too large.']);
            exit;
        }
    }

    private function ensureFileWasUploaded(string $filePath) : void
    {
        if ( ! is_uploaded_file($filePath)) {
            header('HTTP/1.1 500 Server Error');
            $this->renderJsonResponse(['File was not uploaded.']);
            exit;
        }
    }

    private function ensureFileTypeIsValid(string $fileType) : void
    {
        if ( ! in_array($fileType, Image::ALLOWED_TYPES, true)) {
            header('HTTP/1.1 400 Invalid file type.');
            $this->renderJsonResponse(['File type not allowed.']);
            exit;
        }
    }

    private function ensureIsUploadRequest(?array $file) : void
    {
        if ($file === null) {
            header('HTTP/1.1 400 Bad Request');
            $this->renderJsonResponse(['No file uploaded']);
            exit;
        }
    }

    private function renderCouldNotUploadFile() : never
    {
        header('HTTP/1.1 500 Server Error');
        $this->renderJsonResponse(['Could not upload file.']);
        exit;
    }
}
