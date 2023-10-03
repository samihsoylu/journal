<?php

declare(strict_types=1);

use SamihSoylu\Journal\Infrastructure\Adapter\Action\Synchronous\SynchronousActionDispatcher;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Fake\FakeAction;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyAction;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Spy\SpyActionHandler;
use SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub\StubContainer;

it('dispatches the action to the corresponding handler', function (): void {
    $spyAction = new SpyAction();
    $spyActionHandler = new SpyActionHandler();

    $stubContainer = new StubContainer([
        SpyActionHandler::class => $spyActionHandler,
    ]);

    $dispatcher = new SynchronousActionDispatcher($stubContainer);
    $dispatcher->dispatch($spyAction);

    $spyActionHandler->assertInvokedWith($spyAction);
});

it('throws an exception if handler class does not exist', function (): void {
    $fakeAction = new FakeAction();

    $stubContainer = new StubContainer();

    $dispatcher = new SynchronousActionDispatcher($stubContainer);
    $dispatcher->dispatch($fakeAction);
})->throws(InvalidArgumentException::class);
