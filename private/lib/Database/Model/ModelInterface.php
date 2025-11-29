<?php

declare(strict_types=1);

namespace App\Database\Model;

interface ModelInterface
{
    /**
     * This method must return the `id` column from a table row.
     */
    public function getId() : int;

    /**
     * This method must set a new timestamp of 'now' to the `lastUpdatedTimestamp` column within a table.
     */
    public function setLastUpdatedTimestamp() : void;

    /**
     * This method must return the `lastUpdatedTimestamp` column from a table row.
     *
     * @return int Unix timestamp
     */
    public function getLastUpdatedTimestamp() : int;

    /**
     * This method must return the `createdTimestamp` column from a table row.
     */
    public function getCreatedTimestamp() : int;
}
