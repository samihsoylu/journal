<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core;

final readonly class TestPath
{
    public function __construct(
        private string $testDoubleDirPath,
    ) {}

    public function getFakeTestDoublePath(): string
    {
        return $this->testDoubleDirPath . '/Fake';
    }
}