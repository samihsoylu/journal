<?php

declare(strict_types=1);

use SamihSoylu\Journal\Framework\Event\Provider\EventSubscriberProvider;
use Symfony\Component\Finder\Finder;

it('returns an empty array when no event listener files are found', function () {
    $finderMock = Mockery::mock(Finder::class);
    $finderMock->shouldReceive('files')->once()->andReturnSelf();
    $finderMock->shouldReceive('in')->once()->andReturnSelf();
    $finderMock->shouldReceive('name')->once()->andReturnSelf();
    $finderMock->shouldReceive('hasResults')->once()->andReturn(false);

    $locator = new EventSubscriberProvider($finderMock, 'some/dir');

    expect($locator->findEventSubscriberFiles())->toBe([]);
});

it('returns an array of event listener files when found', function () {
    $finderMock = Mockery::mock(Finder::class);
    $finderMock->shouldReceive('files')->once()->andReturnSelf();
    $finderMock->shouldReceive('in')->once()->andReturnSelf();
    $finderMock->shouldReceive('name')->once()->andReturnSelf();
    $finderMock->shouldReceive('hasResults')->once()->andReturn(true);

    $mockIterator = new ArrayIterator(['file1', 'file2']);
    $finderMock->shouldReceive('getIterator')->once()->andReturn($mockIterator);

    $locator = new EventSubscriberProvider($finderMock, 'some/dir');

    expect($locator->findEventSubscriberFiles())->toEqual(['file1', 'file2']);
});
