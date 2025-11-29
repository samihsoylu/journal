<?php

declare(strict_types=1);

namespace Tests\Database\Model;

use App\Database\Model\AbstractModel;
use App\Database\Model\ModelInterface;
use App\Database\Model\User;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UserTest extends TestCase
{
    public function testThatWeCanGetFields() : void
    {
        $expectedUsername = 'michael';
        $expectedPassword = 'm!ch4el#1';
        $expectedEmail = 'm.scott@mail.io';
        $expectedPrivilegeLevel = User::PRIVILEGE_LEVEL_ADMIN;

        $user = new User();
        $user->setUsername($expectedUsername)
            ->setPassword($expectedPassword)
            ->setEmailAddress($expectedEmail)
            ->setPrivilegeLevel($expectedPrivilegeLevel)
        ;
        $user->setLastUpdatedTimestamp();

        // Checks if the user model extends AbstractModel and implements ModelInterface
        self::assertInstanceOf(ModelInterface::class, $user);
        self::assertInstanceOf(AbstractModel::class, $user);

        // Checks if set values equal the values we retrieve
        self::assertSame($expectedUsername, $user->getUsername());
        self::assertSame($expectedPassword, $user->getPassword());
        self::assertSame($expectedEmail, $user->getEmailAddress());
        self::assertSame($expectedPrivilegeLevel, $user->getPrivilegeLevel());
        self::assertIsInt($user->getLastUpdatedTimestamp(), 'Expected unix timestamp, must return int');

        $date = new DateTime("@{$user->getLastUpdatedTimestamp()}");

        if ($user->getTimezone() !== null) {
            $date->setTimezone(new DateTimeZone($user->getTimezone()));
        }

        $expectedFormattedTimestamp = $date->format('d M Y H:i');

        self::assertSame($expectedFormattedTimestamp, $user->getLastUpdatedTimestampFormatted());
    }
}
