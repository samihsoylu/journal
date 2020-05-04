<?php declare(strict_types=1);

namespace App\Database\Models;

interface ModelInterface
{
    /**
     * This method must return the `id` column from a table row.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * This method must set a new timestamp of 'now' to the `lastUpdatedTimestamp` column within a table
     *
     * @return void
     */
    public function setLastUpdatedTimestamp(): void;

    /**
     * This method must return the `lastUpdatedTimestamp` column from a table row.
     *
     * @return int Unix timestamp
     */
    public function getLastUpdatedTimestamp(): int;

    /**
     * This method must return the `createdTimestamp` column from a table row.
     *
     * @return void
     */
    public function getCreatedTimestamp(): int;
}
