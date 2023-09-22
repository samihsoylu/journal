<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Framework;

enum Environment: string
{
    case PROD = 'prod';
    case TEST = 'test';
    case DEV  = 'dev';

    public function isProd(): bool
    {
        return $this === self::PROD;
    }

    public function isTest(): bool
    {
        return $this === self::TEST;
    }

    public function isDev(): bool
    {
        return $this === self::DEV;
    }
}