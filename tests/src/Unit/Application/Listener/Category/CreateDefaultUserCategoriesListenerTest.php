<?php

declare(strict_types=1);

use SamihSoylu\Journal\Application\Component\Category\UseCase\Create\CreateDefaultCategoriesAction;
use SamihSoylu\Journal\Application\Component\User\Event\UserCreatedEvent;
use SamihSoylu\Journal\Application\Listener\Category\CreateDefaultUserCategoriesListener;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyActionDispatcher;

it('should dispatch action create default categories', function (): void {
    $expectedUserId = 'QWOIUYTRD';
    $expectedPasswordTransientId = 'ABCDEF';
    $spyActionDispatcher = new SpyActionDispatcher();

    $listener = new CreateDefaultUserCategoriesListener($spyActionDispatcher);
    $listener->__invoke(new UserCreatedEvent($expectedUserId, $expectedPasswordTransientId));

    $spyActionDispatcher->assertActionDispatchedWithParameters(
        expectedAction: CreateDefaultCategoriesAction::class,
        expectedParameters: [
            'userId' => $expectedUserId,
            'passwordTransientId' => $expectedPasswordTransientId,
        ]
    );
});
