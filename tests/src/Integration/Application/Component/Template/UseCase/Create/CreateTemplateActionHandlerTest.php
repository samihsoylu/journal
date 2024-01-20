<?php

declare(strict_types=1);

use Ramsey\Uuid\Rfc4122\UuidV4;
use SamihSoylu\Journal\Application\Component\Template\Event\TemplateCreatedEvent;
use SamihSoylu\Journal\Application\Component\Template\UseCase\Create\CreateTemplateAction;
use SamihSoylu\Journal\Application\Component\Template\UseCase\Create\CreateTemplateActionHandler;
use SamihSoylu\Journal\Domain\Repository\CategoryRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\TemplateRepositoryInterface;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyCategoryRepository;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyEventDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyTemplateRepository;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyUserRepository;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyEventDispatcher;

it('should throw when user is not found', function (): void {
    $handler = new CreateTemplateActionHandler(
        new DummyUserRepository(),
        new DummyCategoryRepository(),
        new DummyTemplateRepository(),
        new DummyEventDispatcher(),
    );
    $handler->__invoke(
        new CreateTemplateAction(
            title: 'Some title',
            content: 'Some content',
            userId: UuidV4::uuid4()->toString(),
            categoryId: UuidV4::uuid4()->toString(),
        )
    );
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessageMatches('/User\[id=[^\]]+\] not found/');

it('should throw when category is not found', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()->save();

    $handler = new CreateTemplateActionHandler(
        testKit()->getService(UserRepositoryInterface::class),
        new DummyCategoryRepository(),
        new DummyTemplateRepository(),
        new DummyEventDispatcher(),
    );
    $handler->__invoke(
        new CreateTemplateAction(
            title: 'Some title',
            content: 'Some content',
            userId: $user->getId()->toString(),
            categoryId: UuidV4::uuid4()->toString(),
        )
    );
})->throws(UnexpectedValueException::class)
    ->expectExceptionMessageMatches('/Category\[id=[^\]]+\] not found/');

it('should throw when found category does not belong to the user', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()
        ->withUser($user)
        ->save();
    $anotherUser = testKit()->testDbPopulator()->createNewUser()
        ->withUsername('AnotherUsername')
        ->save();

    $handler = new CreateTemplateActionHandler(
        testKit()->getService(UserRepositoryInterface::class),
        testKit()->getService(CategoryRepositoryInterface::class),
        new DummyTemplateRepository(),
        new DummyEventDispatcher(),
    );
    $handler->__invoke(
        new CreateTemplateAction(
            title: 'Some title',
            content: 'Some content',
            userId: $anotherUser->getId()->toString(),
            categoryId: $category->getId()->toString(),
        )
    );
})->throws(LogicException::class)
    ->expectExceptionMessageMatches('/Category\[id=[^\]]+\] does not belong to User\[id=[^\]]+\]/');

it('should create a template', function (): void {
    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()
        ->withUser($user)
        ->save();

    $expectedFields = [
        'title' => 'Some title',
        'content' => 'Some content',
        'userId' => $user->getId()->toString(),
        'categoryId' => $category->getId()->toString(),
    ];

    $handler = new CreateTemplateActionHandler(
        testKit()->getService(UserRepositoryInterface::class),
        testKit()->getService(CategoryRepositoryInterface::class),
        testKit()->getService(TemplateRepositoryInterface::class),
        new DummyEventDispatcher(),
    );
    $handler->__invoke(
        new CreateTemplateAction(
            title: $expectedFields['title'],
            content: $expectedFields['content'],
            userId: $expectedFields['userId'],
            categoryId: $expectedFields['categoryId'],
        )
    );

    $expectedTemplate = testKit()->testOrm()->fetchOneAssoc('SELECT * FROM Template');
    foreach ($expectedFields as $expectedFieldName => $expectedFieldValue) {
        expect($expectedTemplate[$expectedFieldName])->toEqual($expectedFieldValue);
    }
});

it('should dispatch template created event', function (): void {
    $spyEventDispatcher = new SpyEventDispatcher();

    $user = testKit()->testDbPopulator()->createNewUser()->save();
    $category = testKit()->testDbPopulator()->createNewCategory()
        ->withUser($user)
        ->save();

    $handler = new CreateTemplateActionHandler(
        testKit()->getService(UserRepositoryInterface::class),
        testKit()->getService(CategoryRepositoryInterface::class),
        testKit()->getService(TemplateRepositoryInterface::class),
        $spyEventDispatcher,
    );
    $handler->__invoke(
        new CreateTemplateAction(
            title: 'Some title',
            content: 'Some content',
            userId: $user->getId()->toString(),
            categoryId: $category->getId()->toString(),
        )
    );

    $expectedTemplate = testKit()->testOrm()->fetchOneAssoc('SELECT * FROM Template');
    $spyEventDispatcher->assertEventDispatchedWithParameters(
        expectedEvent: TemplateCreatedEvent::class,
        expectedParameters: [
            'userId' => $user->getId()->toString(),
            'templateId' => $expectedTemplate['id'],
        ],
    );
});
