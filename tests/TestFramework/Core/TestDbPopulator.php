<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core;

use DI\Container;
use Psr\Container\ContainerInterface;
use SamihSoylu\Journal\Tests\TestFramework\Seed\CategorySeed;
use SamihSoylu\Journal\Tests\TestFramework\Seed\EntrySeed;
use SamihSoylu\Journal\Tests\TestFramework\Seed\TemplateSeed;
use SamihSoylu\Journal\Tests\TestFramework\Seed\UserSeed;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;

final class TestDbPopulator
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function createNewUser(): UserSeed
    {
        return $this->container->make(UserSeed::class);
    }

    public function createNewCategory(): CategorySeed
    {
        return $this->container->make(CategorySeed::class);
    }

    public function createNewEntry(): EntrySeed
    {
        return $this->container->make(EntrySeed::class);
    }

    public function createNewTemplate(): TemplateSeed
    {
        return $this->container->make(TemplateSeed::class);
    }
}