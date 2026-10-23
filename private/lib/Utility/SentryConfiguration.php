<?php

declare(strict_types=1);

namespace App\Utility;

final class SentryConfiguration
{
    public static function options(string $dsn) : array
    {
        return [
            'dsn' => $dsn,
            'send_default_pii' => false,
            'max_request_body_size' => 'never',
            'before_send' => new SentryEventSanitizer(),
        ];
    }
}
