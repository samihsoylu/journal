<?php

declare(strict_types=1);

use SamihSoylu\Journal\Application\Component\Category\Event\DefaultCategoriesCreatedEvent;
use SamihSoylu\Journal\Application\Component\Category\UseCase\Create\CreateDefaultCategoriesAction;
use SamihSoylu\Journal\Application\Component\Category\UseCase\Create\CreateDefaultCategoriesActionHandler;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyCategoryRepository;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyEventDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyEventDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubCache;

it('should create three default categories for a user', function (): void {
    // Setup
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $handler = new CreateDefaultCategoriesActionHandler(
        testKit()->getService(UserRepositoryInterface::class),
        testKit()->getService(CategoryRepositoryInterface::class),
        new DummyEventDispatcher(),
    );

    // Act
    $handler->__invoke(new CreateDefaultCategoriesAction(
        $user->getId()->toString(),
        StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
    ));

    // Assert
    $rows = testKit()->testOrm()->fetchAllAssoc(
        'SELECT * FROM Category WHERE userId = :userId',
        ['userId' => $user->getId()->toString()]
    );

    expect($rows)->toHaveCount(3);
});

it('should throw an error when a user is not found', function (): void {
    // Setup
    $handler = new CreateDefaultCategoriesActionHandler(
        testKit()->getService(UserRepositoryInterface::class),
        new DummyCategoryRepository(),
        new DummyEventDispatcher(),
    );

    // Act
    $handler->__invoke(new CreateDefaultCategoriesAction(
        '855bb095-c4f7-4749-8dfe-44a0f255455d',
        StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
    ));
})->throws(UnexpectedValueException::class, 'User[id=855bb095-c4f7-4749-8dfe-44a0f255455d] not found');

it('should dispatch a default categories created event', function (): void {
    // Setup
    $spyEventDispatcher = new SpyEventDispatcher();
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $handler = new CreateDefaultCategoriesActionHandler(
        testKit()->getService(UserRepositoryInterface::class),
        new DummyCategoryRepository(),
        $spyEventDispatcher,
    );

    // Act
    $handler->__invoke(new CreateDefaultCategoriesAction(
        $user->getId()->toString(),
        StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
    ));

    // Assert
    $spyEventDispatcher->assertEventDispatchedWithParameters(
        DefaultCategoriesCreatedEvent::class,
        [
            'userId' => $user->getId()->toString(),
            'passwordTransientId' => StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
        ],
    );
});
