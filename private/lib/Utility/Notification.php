<?php declare(strict_types=1);

namespace App\Utility;

/**
 * Class Notification is used to transfer user notifications between redirects in the web app using Sessions
 */
class Notification
{
    protected const NOTIFICATION_TYPE    = 'notify_type';
    protected const NOTIFICATION_MESSAGE = 'notify_message';

    public const TYPE_INFO    = 'info';
    public const TYPE_SUCCESS = 'success';
    public const TYPE_WARNING = 'warning';
    public const TYPE_ERROR   = 'error';

    protected array $allowedMessageTypes = [
        self::TYPE_INFO,
        self::TYPE_SUCCESS,
        self::TYPE_WARNING,
        self::TYPE_ERROR,
    ];

    /**
     * Sets two notification sessions, type and message.
     *
     * @param string $type info|success|warning|error
     * @param string $message
     */
    public function set(string $type, string $message): void
    {
        if (!in_array($type, $this->allowedMessageTypes, true)) {
            throw new \RuntimeException("Invalid type '{$type}' provided");
        }

        Session::put(self::NOTIFICATION_TYPE, $type);
        Session::put(self::NOTIFICATION_MESSAGE, $message);
    }

    /**
     * Gets the existing sessions returns [type, message]
     *
     * @return array
     */
    public function get(): array
    {
        $notificationData = [
            Session::get(self::NOTIFICATION_TYPE),
            Session::get(self::NOTIFICATION_MESSAGE),
        ];

        $this->flush();
        return $notificationData;
    }

    /**
     * Checks to see if a notification session is set. Notifications are stored in sessions so that they are not lost
     * in between redirects on the web application.
     *
     * @return bool true - notification exists, false - not found
     */
    public function exists(): bool
    {
        return Session::exists(self::NOTIFICATION_TYPE) &&
            Session::exists(self::NOTIFICATION_MESSAGE);
    }

    /**
     * Delete current existing notification sessions
     *
     * @return void
     */
    protected function flush(): void
    {
        Session::delete(self::NOTIFICATION_TYPE);
        Session::delete(self::NOTIFICATION_MESSAGE);
    }
}