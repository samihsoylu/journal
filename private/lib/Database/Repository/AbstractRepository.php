<?php

namespace App\Database\Repository;

use App\Database\Database;
use App\Database\Model\ModelInterface;
use \Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use \Doctrine\ORM\OptimisticLockException;

abstract class AbstractRepository
{
    /**
     * @var EntityManager $db - database instance
     */
    protected $db;

    protected const RESOURCE_NAME = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAll(): array
    {
        $resource = $this->db->getRepository(static::RESOURCE_NAME);
        return $resource->findAll();
    }

    public function getById(int $id): object
    {
        $modelName = static::RESOURCE_NAME;
        $resource  = $this->db->find($modelName, $id);

        if ($resource === null) {
            throw new \RuntimeException("{$modelName} with id={$id} was not found");
        }

        return $resource;
    }

    /**
     * Queue model changes to be saved, later when using the $this->save() method, the queued changes will go in to
     * effect.
     *
     * @param ModelInterface $model
     * @return void
     *
     * @throws ORMException
     */
    public function queue(ModelInterface $model): void
    {
        // Update model timestamps when ever queued to be saved
        $model->setCreatedTimestamp();
        $model->setLastUpdatedTimestamp();

        // Queue this model to list of models that will be saved
        $this->db->persist($model);
    }

    /**
     * Queue model to be removed from the database. This function call must follow with the save method for the model
     * changes to go in to effect.
     *
     * @param ModelInterface $model
     * @return void
     *
     * @throws ORMException
     */
    public function remove(ModelInterface $model): void
    {
        $this->db->remove($model);
    }

    /**
     * Saves all queued changes to the database
     *
     * @return void
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save(): void
    {
        $this->db->flush();
    }
}