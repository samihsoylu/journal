<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\Framework\Core;

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Tests\Framework\Core\TestOrm\TestOrmInterface;

final class TestKit
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly TestOrmInterface $testOrm,
        private readonly TestDbPopulator $testDbPopulator,
    ) {}

    public function testOrm(): TestOrmInterface
    {
        return $this->testOrm;
    }

    public function testDbPopulator(): TestDbPopulator
    {
        return $this->testDbPopulator;
    }

    /**
     * @template T
     *
     * @param class-string<T> $serviceId
     * @return T
     */
    public function getService(string $serviceId): object
    {
        return $this->container->get($serviceId);
    }
}