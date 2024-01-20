<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use SamihSoylu\Journal\Application\Component\User\Event\UserCreatedEvent;
use SamihSoylu\Journal\Application\Component\User\UseCase\Create\CreateUserAction;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestSetupHelper\SetupCreateUserActionHandler\CreateForSavingUserToDbDto;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyEventDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubCache;

it('should successfully save a user into the database', function (): void {
    // Setup
    $userDto = new CreateForSavingUserToDbDto(
        'jane',
        'jane@mail.com',
        Role::OWNER,
        '01HMM776DK0ZSFD31QCGX4CNT6',
        '0800fc577294c34e0b28ad2839435945',
    );
    $handler = testKit()->testSetupHelper()->createUserActionHandler()->createForSavingUserToDb($userDto);

    // Act
    $handler->__invoke(
        new CreateUserAction(
            $userDto->expectedUsername,
            StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
            $userDto->expectedEmail,
            $userDto->expectedRole,
        )
    );

    // Assert
    $user = testKit()->testOrm()->fetchOneAssoc("SELECT * FROM `User`");
    expect($user['username'])->toEqual($userDto->expectedUsername)
        ->and($user['emailAddress'])->toEqual($userDto->expectedEmail)
        ->and($user['password'])->toEqual($userDto->expectedHashedPassword)
        ->and($user['role'])->toEqual($userDto->expectedRole->value)
        ->and($user['protectedKey'])->toEqual($userDto->expectedProtectedKeyForDb);
});

it('should throw when password is not found in transient cache', function (): void {
    // Setup
    $handler = testKit()->testSetupHelper()->createUserActionHandler()->createWithDummyDependencies();

    // Act
    $handler->__invoke(new CreateUserAction(
        'jane',
        StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
        'jane@mail.com',
        Role::OWNER,
    ));
})->throws(UnexpectedValueException::class, 'CacheItem[transientId=ABCDEF] not found');

it('should dispatch a user created event', function (): void {
    // Setup
    $expectedUserId = Uuid::uuid4();
    $spyEventDispatcher = new SpyEventDispatcher();
    $handler = testKit()->testSetupHelper()
        ->createUserActionHandler()
        ->createForDispatchingAnEvent($expectedUserId, $spyEventDispatcher);

    // Act
    $handler->__invoke(
        new CreateUserAction(
            'jane',
            StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
            'jane@mail.com',
            Role::OWNER
        )
    );

    // Assert
    $spyEventDispatcher->assertEventDispatchedWithParameters(
        UserCreatedEvent::class,
        [
            'userId' => $expectedUserId->toString(),
            'passwordTransientId' => StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
        ]
    );
});
