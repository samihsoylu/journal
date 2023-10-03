<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Stub;

use Psr\Container\ContainerInterface;

final readonly class StubContainer implements ContainerInterface
{
    public function __construct(
        private array $registry = [],
    ) {}

    public function get(string $id)
    {
        return $this->registry[$id];
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->registry);
    }
}
