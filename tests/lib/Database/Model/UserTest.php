<?php declare(strict_types=1);

namespace Tests\Database\Model;

use App\Database\Model\AbstractModel;
use App\Database\Model\ModelInterface;
use App\Database\Model\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testThatWeCanGetFields(): void
    {
        $expectedUsername = 'michael';
        $expectedPassword = 'm!ch4el#1';
        $expectedEmail = 'm.scott@mail.io';
        $expectedPrivilegeLevel = User::PRIVILEGE_LEVEL_ADMIN;

        $user = new User();
        $user->setUsername($expectedUsername)
            ->setPassword($expectedPassword)
            ->setEmailAddress($expectedEmail)
            ->setPrivilegeLevel($expectedPrivilegeLevel);
        $user->setLastUpdatedTimestamp();

        // Checks if the user model extends AbstractModel and implements ModelInterface
        $this->assertInstanceOf(ModelInterface::class, $user);
        $this->assertInstanceOf(AbstractModel::class, $user);

        // Checks if set values equal the values we retrieve
        $this->assertEquals($expectedUsername, $user->getUsername());
        $this->assertEquals($expectedPassword, $user->getPassword());
        $this->assertEquals($expectedEmail, $user->getEmailAddress());
        $this->assertEquals($expectedPrivilegeLevel, $user->getPrivilegeLevel());
        $this->assertIsInt($user->getLastUpdatedTimestamp(), 'Expected unix timestamp, must return int');

        $date = new \DateTime("@{$user->getLastUpdatedTimestamp()}");
        if ($user->getTimezone() !== null) {
            $date->setTimezone(new \DateTimeZone($user->getTimezone()));
        }

        $expectedFormattedTimestamp = $date->format('d M Y H:i');

        $this->assertEquals($expectedFormattedTimestamp, $user->getLastUpdatedTimestampFormatted());
    }
}
