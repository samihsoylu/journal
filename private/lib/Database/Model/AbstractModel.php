<?php declare(strict_types=1);

namespace App\Database\Model;

use App\Database\Database;

/**
 * Class AbstractModel represents all tables in the database. The properties defined in this file are present in every
 * table. Columns such as id, created & updated dates are enforced here on to all models.
 *
 * @package App\Database\Models
 */
abstract class AbstractModel implements ModelInterface
{
    protected int $id;
    protected int $createdTimestamp;
    protected int $lastUpdatedTimestamp;

    public function __construct()
    {
        $this->createdTimestamp = time();
    }

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
     * Set the last updated time stamp of this model to now.
     *
     * @return void
     */
    public function setLastUpdatedTimestamp(): void
    {
        $this->lastUpdatedTimestamp = time();
    }

    public function getLastUpdatedTimestampFormatted(?string $timezone = null): string
    {
        return $this->formatTimestamp($this->getLastUpdatedTimestamp(), $timezone);
    }

    public function getCreatedTimestampFormatted(?string $timezone = null): string
    {
        return $this->formatTimestamp($this->getCreatedTimestamp(), $timezone);
    }

    private function formatTimestamp(int $timestamp, ?string $timezone = null): string
    {
        $date = new \DateTime("@{$timestamp}");

        if ($timezone !== null) {
            $date->setTimezone(new \DateTimeZone($timezone));
        }

        return $date->format('d M Y H:i');
    }

    public static function getClassName(): string
    {
        return str_replace(__NAMESPACE__ . '\\', '', static::class);
    }

    public function save()
    {
        $this->setLastUpdatedTimestamp();

        $db = Database::getInstance()->getEntityManager();
        $db->persist($this);
        $db->flush();
    }
}
