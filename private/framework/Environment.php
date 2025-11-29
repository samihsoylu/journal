<?php

declare(strict_types=1);

namespace App\Framework;

final class Environment
{
    public const DEV = 'dev';
    public const TEST = 'test';
    public const PROD = 'prod';
    private string $value;

    private function __construct(string $value)
    {
        $allowed = [self::DEV, self::TEST, self::PROD];
        if (!in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid environment: {$value}");
        }
        $this->value = $value;
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isDev(): bool
    {
        return $this->value === self::DEV;
    }

    public function isTest(): bool
    {
        return $this->value === self::TEST;
    }

    public function isProd(): bool
    {
        return $this->value === self::PROD;
    }
}
