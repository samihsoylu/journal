<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\UserException\InvalidParameterException;
use App\Service\AuthenticationService;
use App\Service\MediaService;
use App\Service\Security\ImageContentValidator;
use App\Service\ValueObject\Image;
use App\Utility\Template;
use App\Validator\MediaValidator;
use Exception;

final class Media extends AbstractController
{
    public const string MEDIA_URL = BASE_URL . '/media';
    public const string MEDIA_UPLOAD_POST_URL = self::MEDIA_URL . '/upload';
    public const string MEDIA_GET_URL = self::MEDIA_URL . '/{imageName}';

    private readonly MediaValidator $validator;

    public function __construct(
        AuthenticationService $authenticationService,
        Template $template,
        public MediaService $service,
        private readonly ImageContentValidator $imageValidator = new ImageContentValidator(),
    ) {
        parent::__construct($authenticationService, $template);
        $this->redirectLoggedOutUsersToLoginPage();
        $this->validator = new MediaValidator($_POST, [], $authenticationService->getUserSession());
    }

    public function upload() : void
    {
        try {
            $this->validator->upload();
        } catch (InvalidParameterException) {
            http_response_code(403);
            $this->renderJsonResponse(['error' => 'Invalid Form Key. Please reload the editor.']);

            return;
        }
        $file = $_FILES['file'] ?? null;

        if ( ! is_array($file) || ! is_string($file['name'] ?? null) || ! is_string($file['tmp_name'] ?? null) || ! is_int($file['size'] ?? null) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ! is_uploaded_file($file['tmp_name'])) {
            http_response_code(400);
            $this->renderJsonResponse(['error' => 'No valid image was uploaded.']);

            return;
        }
        $limit = 1024 * 1024 * IMAGE_UPLOAD_SIZE_LIMIT;

        if ($file['size'] < 1 || $file['size'] > $limit) {
            http_response_code(413);
            $this->renderJsonResponse(['error' => 'The image exceeds the upload size limit.']);

            return;
        }
        $binary = file_get_contents($file['tmp_name']);

        try {
            $type = $this->imageValidator->validate($binary === false ? '' : $binary, $limit);
        } catch (InvalidParameterException $exception) {
            http_response_code(400);
            $this->renderJsonResponse(['error' => $exception->getMessage()]);

            return;
        }
        // Determine the type from the actual image, never the filename or MIME sent by the browser.
        $image = new Image(base64_encode($binary), bin2hex(random_bytes(20)), $type);
        $uploaded = $this->service->encryptImage($this->getUserId(), $image, $this->getUserEncryptionKey(), $file['tmp_name']);

        if ( ! $uploaded) {
            http_response_code(500);
            $this->renderJsonResponse(['error' => 'Could not upload the image.']);

            return;
        }
        $this->renderJsonResponse(['location' => self::MEDIA_URL . '/' . $image->getName()]);
    }

    public function showImage() : void
    {
        $imageName = $this->getRouteParameters()['imageName'] ?? '';

        try {
            $image = $this->service->getDecryptedImage($this->getUserId(), $imageName, $this->getUserEncryptionKey());
        } catch (Exception) {
            http_response_code(404);
            $this->renderTemplate('errors/404');

            return;
        }
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
        header('X-Content-Type-Options: nosniff');
        header('Content-Type: image/' . ($image->getType() === 'jpg' ? 'jpeg' : $image->getType()));
        echo $image->getBinary();
    }
}
