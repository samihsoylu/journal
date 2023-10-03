<?php

declare(strict_types=1);

test('classes only access the domain layer from application or presentation layer')
    ->expect('SamihSoylu\Journal\Domain')
    ->toOnlyBeUsedIn(['SamihSoylu\Journal\Application', 'SamihSoylu\Journal\Domain', 'SamihSoylu\Journal\Presentation']);

test('classes only access the application layer from the presentation layer')
    ->expect('SamihSoylu\Journal\Application')
    ->toOnlyBeUsedIn(['SamihSoylu\Journal\Presentation', 'SamihSoylu\Journal\Application']);

test('classes do not directly call adapters from the infrastructure layer')
    ->expect('SamihSoylu\Journal\Infrastructure\Adapter')
    ->toOnlyBeUsedIn([]);
