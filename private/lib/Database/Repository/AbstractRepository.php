<?php

declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\ModelInterface;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractRepository
{
    /**
     * @var string RESOURCE_NAME name of the database model (name of table)
     */
    public const RESOURCE_NAME = '';

    public function __construct(
        protected EntityManagerInterface $db,
    ) {}

    /**
     * Retrieves all entries from the database table of RESOURCE_NAME.
     */
    public function getAll() : array
    {
        return $this->db->getRepository(static::RESOURCE_NAME)->findAll();
    }

    /**
     * Retrieves a single row from a table by the provided record id, and return found record as a Model.
     */
    public function getById(int $id) : ?object
    {
        return $this->db->find(static::RESOURCE_NAME, $id);
    }

    /**
     * Queue model changes to be saved, later when using the $this->save() method, the queued changes will go in to
     * effect.
     */
    public function queue(ModelInterface $model) : void
    {
        $model->setLastUpdatedTimestamp();

        // Queue this model to list of models that will be saved
        $this->db->persist($model);
    }

    /**
     * Queue model to be removed from the database. This function call must follow with the save method for the model
     * changes to go in to effect.
     */
    public function remove(ModelInterface $model) : void
    {
        $this->db->remove($model);
    }

    /**
     * Saves all queued changes to the database.
     */
    public function save() : void
    {
        $this->db->flush();
    }
}
