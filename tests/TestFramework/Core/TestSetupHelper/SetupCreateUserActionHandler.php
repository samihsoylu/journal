<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core\TestSetupHelper;

use Psr\Container\ContainerInterface;
use Ramsey\Uuid\UuidInterface;
use SamihSoylu\Journal\Application\Component\User\UseCase\Create\CreateUserActionHandler;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestSetupHelper\SetupCreateUserActionHandler\CreateForSavingUserToDbDto;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyCache;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyEventDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyPasswordHasher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyPasswordKeyManager;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyUserRepository;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyEventDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubCache;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubPasswordHasher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubPasswordKeyManager;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubUserRepository;

final readonly class SetupCreateUserActionHandler
{
    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function createWithDummyDependencies(): CreateUserActionHandler
    {
        return new CreateUserActionHandler(
            new DummyCache(),
            new DummyUserRepository(),
            new DummyPasswordKeyManager(),
            new DummyEventDispatcher(),
            new DummyPasswordHasher(),
        );
    }

    public function createForSavingUserToDb(CreateForSavingUserToDbDto $userDto): CreateUserActionHandler
    {
        $stubCache = new StubCache();
        $stubCache->set(StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD, 'some-plain-password');

        return new CreateUserActionHandler(
            $stubCache,
            $this->container->get(UserRepositoryInterface::class),
            new StubPasswordKeyManager(createProtectedKeyForDbWillReturn: $userDto->expectedProtectedKeyForDb),
            new DummyEventDispatcher(),
            new StubPasswordHasher(hashWillReturn: $userDto->expectedHashedPassword),
        );
    }

    public function createForDispatchingAnEvent(
        UuidInterface $expectedUserId,
        SpyEventDispatcher $spyEventDispatcher
    ): CreateUserActionHandler {
        $stubCache = new StubCache();
        $stubCache->set(StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD, 'XXXX');
        $stubUserRepository = new StubUserRepository(queueForSavingWillSetId: $expectedUserId);

        return new CreateUserActionHandler(
            $stubCache,
            $stubUserRepository,
            new DummyPasswordKeyManager(),
            $spyEventDispatcher,
            new DummyPasswordHasher(),
        );
    }
}
