<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm;

interface TestOrmInterface
{
    /**
     * @return array<string, mixed>
     */
    public function fetchOneAssoc(string $query, array $params = []): array;

    /**
     * @return array<array<string, mixed>>
     */
    public function fetchAllAssoc(string $query, array $params = []): array;

    public function persist(object $object): void;

    public function remove(object $object): void;
}
