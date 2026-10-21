<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Exception\UserException\InvalidParameterException;

final class ImageContentValidator
{
    public function validate(string $binary, int $maximumBytes) : string
    {
        if ($binary === '' || strlen($binary) > $maximumBytes) {
            throw new InvalidParameterException('The image is empty or exceeds the upload size limit.');
        }
        $info = @getimagesizefromstring($binary);
        $type = is_array($info) ? match ($info[2]) {
            IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif',
            IMAGETYPE_BMP => 'bmp', IMAGETYPE_WEBP => 'webp', default => null,
        } : null;

        if ($type === null || $info[0] < 1 || $info[1] < 1 || $info[0] * $info[1] > 40_000_000) {
            throw new InvalidParameterException('Upload a valid PNG, JPEG, GIF, BMP or WebP image (up to 40 megapixels).');
        }

        return $type;
    }
}
