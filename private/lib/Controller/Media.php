<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\MediaService;
use App\Service\ValueObject\Image;
use App\Utility\Encryptor;

class Media extends AbstractController
{
    public const MEDIA_URL = BASE_URL . '/media';
    public const MEDIA_UPLOAD_POST_URL = self::MEDIA_URL . '/upload';
    public const MEDIA_GET_URL = self::MEDIA_URL . '/{imageName}';

    public MediaService $service;

    public function __construct(array $routeParameters)
    {
        parent::__construct($routeParameters);

        $this->redirectLoggedOutUsersToLoginPage();
        $this->service = new MediaService(new Encryptor());
    }

    public function upload(): void
    {
        $file     = $_FILES['file'] ?? null;
        $name     = $file['name'];
        $tmpName  = $file['tmp_name'];
        $size     = $file['size'];

        $imageType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $file['type'] = $imageType;

        $binary = $this->getUploadedImageBinaryAsBase64Encoded($file);

        $hashedImageName = sha1($name . uniqid());
        $image = new Image($binary, $hashedImageName, $imageType);

        $uploaded = $this->service->encryptImage(
            $this->getUserId(),
            $image,
            $this->getUserEncryptionKey(),
            $tmpName
        );

        if (!$uploaded) {
            $this->renderCouldNotUploadFile();
        }

        $this->renderJsonResponse(['location' => self::MEDIA_URL . "/{$hashedImageName}"]);
    }

    public function showImage(): void
    {
        $imageName = $this->getRouteParameters()['imageName'] ?? '';

        try {
            $image = $this->service->getDecryptedImage(
                $this->getUserId(),
                $imageName,
                $this->getUserEncryptionKey()
            );
        } catch (\Exception $e) {
            $this->renderNotFound();
        }

        header("Content-Type: image/{$image->getType()}");
        echo $image->getBinary();
    }

    /**
     * @return never
     */
    private function renderNotFound(): void
    {
        header("HTTP/1.1 404 Not Found");
        $this->renderTemplate('errors/404');
        exit;
    }

    private function getUploadedImageBinaryAsBase64Encoded(?array $file): string
    {
        $this->ensureIsUploadRequest($file);
        $this->ensureFileWasUploaded($file['tmp_name']);
        $this->ensureFileTypeIsValid($file['type']);
        $this->ensureFileSizeIsWithinLimits($file['size']);

        $image = file_get_contents($file['tmp_name']);
        if (!$image) {
            header("HTTP/1.1 500 Server Error");
            $this->renderJsonResponse(['error' => 'Could not read uploaded file.']);
            exit;
        }

        return base64_encode($image);
    }


    private function ensureFileSizeIsWithinLimits(int $size): void
    {
        // Convert MB to Bytes
        $sizeLimit = 1024 * 1024 * IMAGE_UPLOAD_SIZE_LIMIT;
        if ($size > $sizeLimit) {
            header('HTTP/1.1 400 File size too large.');
            $this->renderJsonResponse(['error' => 'File size is too large.']);
            exit;
        }
    }

    private function ensureFileWasUploaded(string $filePath): void
    {
        if (!is_uploaded_file($filePath)) {
            header("HTTP/1.1 500 Server Error");
            $this->renderJsonResponse(['error' => 'File was not uploaded.']);
            exit;
        }
    }

    private function ensureFileTypeIsValid(string $fileType): void
    {
        if (!in_array($fileType, Image::ALLOWED_TYPES)) {
            header('HTTP/1.1 400 Invalid file type.');
            $this->renderJsonResponse(['error' => 'File type not allowed.']);
            exit;
        }
    }

    private function ensureIsUploadRequest(?array $file): void
    {
        if ($file === null) {
            header('HTTP/1.1 400 Bad Request');
            $this->renderJsonResponse(['error' => 'No file uploaded']);
            exit;
        }
    }

    /**
     * @return never
     */
    private function renderCouldNotUploadFile(): void
    {
        header("HTTP/1.1 500 Server Error");
        $this->renderJsonResponse(['error' => 'Could not upload file.']);
        exit;
    }
}
