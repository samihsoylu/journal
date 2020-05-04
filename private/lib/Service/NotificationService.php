<?php

namespace App\Service;

use App\Utility\Session;

class NotificationService
{
    protected const NOTIFICATION_TYPE    = 'notify_type';
    protected const NOTIFICATION_MESSAGE = 'notify_message';

    protected array $allowedMessageTypes = [
        'info',
        'success',
        'warning',
        'error',
    ];

    public function setNotification(string $type, string $message): void
    {
        if (!in_array($type, $this->allowedMessageTypes, true)) {
            throw new \RuntimeException("Invalid type '{$type}' provided");
        }

        Session::put(self::NOTIFICATION_TYPE, $type);
        Session::put(self::NOTIFICATION_MESSAGE, $message);
    }

    public function getNotification(): array
    {
        $notificationData = [
            Session::get(self::NOTIFICATION_TYPE),
            Session::get(self::NOTIFICATION_MESSAGE),
        ];

        $this->flush();
        return $notificationData;
    }

    public function isHit(): bool
    {
        return Session::exists(self::NOTIFICATION_TYPE) &&
            Session::exists(self::NOTIFICATION_MESSAGE);
    }

    public function flush(): void
    {
        Session::delete(self::NOTIFICATION_TYPE);
        Session::delete(self::NOTIFICATION_MESSAGE);
    }
}