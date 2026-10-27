<?php

declare(strict_types=1);

namespace App\Service\ValueObject;

use Exception;
use JsonSerializable;
use ReturnTypeWillChange;
use Stringable;

final readonly class Image implements JsonSerializable, Stringable
{
    public const string TYPE_JPG = 'jpg';
    public const string TYPE_PNG = 'png';
    public const string TYPE_GIF = 'gif';
    public const string TYPE_BMP = 'bmp';
    public const string TYPE_WEBP = 'webp';
    public const array ALLOWED_TYPES = [
        self::TYPE_JPG,
        self::TYPE_PNG,
        self::TYPE_GIF,
        self::TYPE_BMP,
        self::TYPE_WEBP,
    ];

    public function __construct(
        private string $binary,
        private string $name,
        private string $type,
    ) {
        $this->ensureImageTypeIsAllowed($this->type);
    }

    public function getBinary() : string
    {
        return base64_decode($this->binary, true);
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getName() : string
    {
        return $this->name;
    }

    private function ensureImageTypeIsAllowed(string $type) : void
    {
        if ( ! in_array($type, self::ALLOWED_TYPES, true)) {
            throw new Exception('Invalid image type.');
        }
    }

    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function __toString() : string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR);
    }

    public static function fromString(string $image) : self
    {
        $image = json_decode($image, true, 512, JSON_THROW_ON_ERROR);

        return new self($image['binary'], $image['name'], $image['type']);
    }
}
