<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UserRepositoryTest extends IntegrationTestCase
{
    private UserRepository $repository;

    #[Override]
    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = $this->getRepository(UserRepository::class);
    }

    #[Test]
    public function it_should_save_user() : void
    {
        $user = UserFactory::setup()
            ->withUsername('savetest')
            ->withEmailAddress('savetest@example.com')
            ->create()
        ;

        $row = $this->testOrm->fetchOneAssoc(
            'SELECT * FROM users WHERE id = ?',
            [$user->getId()],
        );

        self::assertNotNull($row, 'User not found in database');
        self::assertSame('savetest', $row['username']);
        self::assertSame('savetest@example.com', $row['emailAddress']);
    }

    #[Test]
    public function it_should_find_user_by_username() : void
    {
        UserFactory::setup()
            ->withUsername('findbyusername')
            ->create()
        ;

        $result = $this->repository->findByUsername('findbyusername');

        self::assertNotNull($result);
        self::assertSame('findbyusername', $result->getUsername());
    }

    #[Test]
    public function it_should_return_null_when_username_not_found() : void
    {
        $result = $this->repository->findByUsername('nonexistent');

        self::assertNull($result);
    }

    #[Test]
    public function it_should_find_user_by_email_address() : void
    {
        UserFactory::setup()
            ->withEmailAddress('findbyemail@example.com')
            ->create()
        ;

        $result = $this->repository->findByEmailAddress('findbyemail@example.com');

        self::assertNotNull($result);
        self::assertSame('findbyemail@example.com', $result->getEmailAddress());
    }

    #[Test]
    public function it_should_get_user_by_id() : void
    {
        $user = UserFactory::setup()
            ->withUsername('getbyid')
            ->create()
        ;

        $result = $this->repository->getById($user->getId());

        self::assertNotNull($result);
        self::assertSame($user->getId(), $result->getId());
        self::assertSame('getbyid', $result->getUsername());
    }

    #[Test]
    public function it_should_get_all_users() : void
    {
        UserFactory::setup()->withUsername('user1')->withEmailAddress('user1@test.com')->create();
        UserFactory::setup()->withUsername('user2')->withEmailAddress('user2@test.com')->create();
        UserFactory::setup()->withUsername('user3')->withEmailAddress('user3@test.com')->create();

        $result = $this->repository->getAll();

        self::assertCount(3, $result);
    }

    #[Test]
    public function it_should_set_privilege_level() : void
    {
        $user = UserFactory::setup()
            ->withPrivilegeLevel(User::PRIVILEGE_LEVEL_ADMIN)
            ->create()
        ;

        $result = $this->repository->getById($user->getId());

        self::assertNotNull($result);
        self::assertSame(User::PRIVILEGE_LEVEL_ADMIN, $result->getPrivilegeLevel());
        self::assertSame('Admin', $result->getPrivilegeLevelAsString());
    }
}
