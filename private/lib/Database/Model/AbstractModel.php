<?php

namespace App\Database\Model;

/**
 * Class AbstractModel represents all tables in the database. The properties defined in this file are present in every
 * table. Columns such as id, created & updated dates are enforced here on to all models.
 *
 * @package App\Database\Model
 */
abstract class AbstractModel implements ModelInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $createdTimestamp;

    /**
     * @var int
     */
    protected $lastUpdatedTimestamp;

    /**
     * Gets the primary key, `id` column associated with the table.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the createdTimestamp column associated with this database table
     *
     * @return int
     */
    public function getCreatedTimestamp(): int
    {
        return $this->createdTimestamp;
    }

    /**
     * Get the lastUpdatedTimestamp column associated with this database table
     *
     * @return int
     */
    public function getLastUpdatedTimestamp(): int
    {
        return $this->lastUpdatedTimestamp;
    }

    /**
     * Set the createdTimestamp of this model to now, and if already set, then do nothing.
     *
     * @return void
     */
    public function setCreatedTimestamp(): void
    {
        // Only set a timestamp for new models
        if ($this->createdTimestamp > 0) {
            return;
        }

        $this->createdTimestamp = time();
    }

    /**
     * Set the last updated time stamp of this model to now.
     *
     * @return void
     */
    public function setLastUpdatedTimestamp(): void
    {
        $this->lastUpdatedTimestamp = time();
    }
}