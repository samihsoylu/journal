<?php declare(strict_types=1);

namespace App\Service\ValueObject;

class Image implements \JsonSerializable
{
    public const TYPE_JPG = 'jpg';
    public const TYPE_PNG = 'png';
    public const TYPE_GIF = 'gif';
    public const TYPE_BMP = 'bmp';
    public const TYPE_WEBP = 'webp';

    public const ALLOWED_TYPES = [
        self::TYPE_JPG,
        self::TYPE_PNG,
        self::TYPE_GIF,
        self::TYPE_BMP,
        self::TYPE_WEBP,
    ];

    private string $binary;
    private string $name;
    private string $type;

    public function __construct(string $binary, string $name, string $type)
    {
        $this->binary = $binary;
        $this->name = $name;
        $this->type = $type;

        $this->ensureImageTypeIsAllowed($type);
    }

    public function getBinary(): string
    {
        return base64_decode($this->binary);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function ensureImageTypeIsAllowed(string $type): void
    {
        if (!in_array($type, self::ALLOWED_TYPES)) {
            throw new \Exception('Invalid image type.');
        }
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize());
    }

    public static function fromString(string $image): self
    {
        $image = json_decode($image, true);

        return new self($image['binary'], $image['name'], $image['type']);
    }
}
