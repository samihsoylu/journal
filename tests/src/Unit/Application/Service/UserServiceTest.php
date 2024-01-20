<?php

declare(strict_types=1);

use SamihSoylu\Journal\Application\Component\User\UseCase\Create\CreateUserAction;
use SamihSoylu\Journal\Application\Service\UserService;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyActionDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyActionDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyCache;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubCache;

it('should store the password in a secure cache in the create user method', function (): void {
    $spyCache = new SpyCache();
    $expectedPassword = 'jane123';

    $service = new UserService(new DummyActionDispatcher(), $spyCache);
    $service->createUser(
        username: 'jane',
        password: $expectedPassword,
        emailAddress: 'jane@mail.com',
        role: Role::OWNER,
    );

    $spyCache->assertValueExists($expectedPassword);
});

it('should invoke create user action with correct parameters in the create user method', function (): void {
    $spyActionDispatcher = new SpyActionDispatcher();
    $secureCache = new StubCache();
    $expectedUsername = 'jane';
    $expectedPassword = 'jane123';
    $expectedEmail = 'jane@mail.com';
    $expectedRole = Role::OWNER;

    $userService = new UserService($spyActionDispatcher, $secureCache);
    $userService->createUser(
        username: $expectedUsername,
        password: $expectedPassword,
        emailAddress: $expectedEmail,
        role: $expectedRole,
    );

    $spyActionDispatcher->assertActionDispatchedWithParameters(
        expectedAction: CreateUserAction::class,
        expectedParameters: [
            'username' => $expectedUsername,
            'passwordTransientCacheId' => $secureCache->getFirstStoredKey(),
            'emailAddress' => $expectedEmail,
            'role' => $expectedRole,
        ]
    );
});
