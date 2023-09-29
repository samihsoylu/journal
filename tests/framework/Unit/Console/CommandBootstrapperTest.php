<?php

declare(strict_types=1);

use SamihSoylu\Journal\Framework\Console\CommandBootstrapper;

it('should correctly bootstrap and run commands', function () {
    $fakeCommandDirPath = testKit()->testPath()->getFakeTestDoublePath() . '/FakeCommand';

    $bootstrapper = new CommandBootstrapper(
        '',
        '',
        '',
        '',
        '',
    );
});