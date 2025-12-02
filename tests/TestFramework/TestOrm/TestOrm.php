<?php

declare(strict_types=1);

namespace Tests\TestFramework\TestOrm;

/**
 * Interface for ORM operations in tests.
 *
 * Provides abstraction for database operations, allowing tests to:
 * - Execute raw SQL queries for assertions
 * - Persist entities to the database
 */
interface TestOrm
{
    /**
     * Execute SQL query and fetch one row as associative array.
     *
     * @param array<int|string, mixed> $params
     * @return null|array<string, mixed>
     */
    public function fetchOneAssoc(string $sql, array $params = []) : ?array;

    /**
     * Execute SQL query and fetch all rows as associative arrays.
     *
     * @param array<int|string, mixed> $params
     * @return array<array<string, mixed>>
     */
    public function fetchAllAssoc(string $sql, array $params = []) : array;

    /**
     * Persist one or more entities to the database.
     */
    public function save(object ...$entities) : void;
}
