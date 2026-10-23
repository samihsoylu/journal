<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/private/lib',
        __DIR__ . '/private/scripts',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/private/cache',
        __DIR__ . '/vendor',
    ])
    ->withPhpSets(
        php85: true,
    )->withAttributesSets(
        symfony: true,
        doctrine: true,
    )
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        privatization: true,
        typeDeclarations: true,
        earlyReturn: true,
    )
;
