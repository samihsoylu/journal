<?php

declare(strict_types=1);

test('all command classes inherit from Symfony\Component\Console\Command\Command')
    ->expect('SamihSoylu\Journal\Presentation\Console')
    ->toExtend('Symfony\Component\Console\Command\Command');
