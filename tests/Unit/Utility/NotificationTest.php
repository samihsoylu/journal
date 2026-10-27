<?php

declare(strict_types=1);

namespace Tests\Unit\Utility;

use App\Utility\Notification;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/** @internal
 * @coversNothing
 */
final class NotificationTest extends TestCase
{
    #[Test]
    public function invalid_types_do_not_replace_a_pending_notification_and_messages_are_consumed_once() : void
    {
        $previous = $_SESSION ?? [];
        $_SESSION = [];
        $notification = new Notification();

        try {
            $notification->set(Notification::TYPE_SUCCESS, 'Saved');

            try {
                $notification->set('unexpected', 'Replace');
                self::fail('Unknown notification type was accepted');
            } catch (RuntimeException) {
                self::assertTrue($notification->exists());
                self::assertSame([Notification::TYPE_SUCCESS, 'Saved'], $notification->get());
                self::assertFalse($notification->exists());
                self::assertSame([null, null], $notification->get());
            }
        } finally {
            $_SESSION = $previous;
        }
    }
}
