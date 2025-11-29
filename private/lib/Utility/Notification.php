<?php

declare(strict_types=1);

namespace App\Utility;

use RuntimeException;

/**
 * To transfer user notifications between redirects, this class creates and removes sessions for this purpose when
 * necessary.
 */
final class Notification
{
    private const string NOTIFICATION_TYPE = 'notify_type';
    private const string NOTIFICATION_MESSAGE = 'notify_message';
    public const string TYPE_INFO = 'info';
    public const string TYPE_SUCCESS = 'success';
    public const string TYPE_WARNING = 'warning';
    public const string TYPE_ERROR = 'error';

    /**
     * Sets two notification sessions, type and message.
     *
     * @param string $type info|success|warning|error
     */
    public function set(string $type, string $message) : void
    {
        $allowedMessageTypes = [
            self::TYPE_INFO,
            self::TYPE_SUCCESS,
            self::TYPE_WARNING,
            self::TYPE_ERROR,
        ];

        if ( ! in_array($type, $allowedMessageTypes, true)) {
            throw new RuntimeException(sprintf("Invalid type '%s' provided", $type));
        }

        Session::put(self::NOTIFICATION_TYPE, $type);
        Session::put(self::NOTIFICATION_MESSAGE, $message);
    }

    /**
     * Gets the existing sessions returns [type, message].
     */
    public function get() : array
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
    public function exists() : bool
    {
        return Session::exists(self::NOTIFICATION_TYPE)
            && Session::exists(self::NOTIFICATION_MESSAGE);
    }

    /**
     * Delete current existing notification sessions.
     */
    private function flush() : void
    {
        Session::delete(self::NOTIFICATION_TYPE);
        Session::delete(self::NOTIFICATION_MESSAGE);
    }
}
