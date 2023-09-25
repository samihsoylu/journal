<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core;

use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;

final readonly class TestKit
{
    public function __construct(
        private ContainerInterface $container,
        private TestOrmInterface $testOrm,
        private TestPath $testPath,
    ) {}

    public function testOrm(): TestOrmInterface
    {
        return $this->testOrm;
    }

    public function testPath(): TestPath
    {
        return $this->testPath;
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