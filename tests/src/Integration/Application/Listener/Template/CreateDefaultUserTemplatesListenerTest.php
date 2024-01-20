<?php

declare(strict_types=1);

use Ramsey\Uuid\Rfc4122\UuidV4;
use SamihSoylu\Journal\Application\Component\Category\Event\DefaultCategoriesCreatedEvent;
use SamihSoylu\Journal\Application\Component\Template\UseCase\Create\CreateTemplateAction;
use SamihSoylu\Journal\Application\Listener\Template\CreateDefaultUserTemplatesListener;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyActionDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyCache;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyCategoryRepository;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyPasswordKeyManager;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyUserRepository;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyActionDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubCache;

it('should dispatch create template action', function (): void {
    // Prepare
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()->withUser($user)->save();
    $stubCache = new StubCache();
    $stubCache->set(StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD, 'fake-password');

    // Set expectations
    $expectedUserId = $user->getId()->toString();
    $expectedCategoryId = $category->getId()->toString();
    $spyActionDispatcher = new SpyActionDispatcher();

    // Act
    $listener = new CreateDefaultUserTemplatesListener(
        $spyActionDispatcher,
        testKit()->getService(CategoryRepositoryInterface::class),
        new DummyPasswordKeyManager(),
        $stubCache,
        testKit()->getService(UserRepositoryInterface::class),
    );
    $listener->__invoke(new DefaultCategoriesCreatedEvent($expectedUserId, StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD));

    // Assert
    $spyActionDispatcher->assertActionDispatchedWithParameters(
        expectedAction: CreateTemplateAction::class,
        expectedParameters: [
            'title' => 'Food',
            'content' => '',
            'userId' => $expectedUserId,
            'categoryId' => $expectedCategoryId,
        ]
    );
});

it('should throw when user is not found', function (): void { // in create default user templates listener
    $listener = new CreateDefaultUserTemplatesListener(
        new DummyActionDispatcher(),
        new DummyCategoryRepository(),
        new DummyPasswordKeyManager(),
        new DummyCache(),
        new DummyUserRepository(),
    );
    $listener->__invoke(new DefaultCategoriesCreatedEvent(
        UuidV4::uuid4()->toString(),
        ''
    ));
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessageMatches('/User\[id=[^\]]+\] not found/');

it('should throw when transient id is not found in cache item', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()->save();

    $listener = new CreateDefaultUserTemplatesListener(
        new DummyActionDispatcher(),
        new DummyCategoryRepository(),
        new DummyPasswordKeyManager(),
        new DummyCache(),
        testKit()->getService(UserRepositoryInterface::class),
    );
    $listener->__invoke(new DefaultCategoriesCreatedEvent(
        $user->getId()->toString(),
        StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
    ));
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessageMatches('/CacheItem\[transientId=[^\]]+\] not found/');

it('should throw when category is not found', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $stubCache = new StubCache();
    $stubCache->set(StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD, 'fake-password');

    $listener = new CreateDefaultUserTemplatesListener(
        new DummyActionDispatcher(),
        new DummyCategoryRepository(),
        new DummyPasswordKeyManager(),
        $stubCache,
        testKit()->getService(UserRepositoryInterface::class),
    );
    $listener->__invoke(new DefaultCategoriesCreatedEvent(
        $user->getId()->toString(),
        StubCache::DEFAULT_KEY_FOR_TRANSIENT_PASSWORD,
    ));
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessageMatches('/Category\[title=[^\]]+\] not found for User\[id=[^\]]+\]/');
