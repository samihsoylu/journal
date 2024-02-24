<?php

declare(strict_types=1);

use SamihSoylu\Journal\Framework\Infrastructure\Adapter\Action\Synchronous\SynchronousActionDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy\DummyAction;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyAction;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyActionHandler;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubContainer;

it('should dispatch the action to the corresponding handler', function (): void {
    $fakeAction = new SpyAction();
    $spyActionHandler = new SpyActionHandler();

    $dispatcher = new SynchronousActionDispatcher(new StubContainer([
        SpyActionHandler::class => $spyActionHandler,
    ]));
    $dispatcher->dispatch($fakeAction);

    $spyActionHandler->assertInvokedWith($fakeAction);
});

it('should throw an exception if the handler class does not exist', function (): void {
    $dispatcher = new SynchronousActionDispatcher(new StubContainer());

    $dispatcher->dispatch(new DummyAction());
})->throws(InvalidArgumentException::class);
