<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Database;
use App\Database\Exception\NotFoundException;
use App\Database\Model\ModelInterface;
use \Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use \Doctrine\ORM\OptimisticLockException;

abstract class AbstractRepository
{
    /** @var EntityManager $db database instance */
    protected EntityManager $db;

    /**
     * @var string RESOURCE_NAME name of the database model (name of table)
     */
    protected const RESOURCE_NAME = '';

    public function __construct()
    {
        $instance = Database::getInstance();
        $this->db = $instance->getEntityManager();
    }

    /**
     * Retrieves all entries from the database table of RESOURCE_NAME
     *
     * @return array
     */
    public function getAll(): array
    {
        $resource = $this->db->getRepository(static::RESOURCE_NAME);
        return $resource->findAll();
    }

    /**
     * Retrieves a single row from a table by the provided record id, and returns it as a Model object.
     *
     * @param int $id
     *
     * @return object
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getById(int $id): object
    {
        $modelName = static::RESOURCE_NAME;
        $resource  = $this->db->find($modelName, $id);

        if (!$resource) {
            throw NotFoundException::entityIdNotFound(self::RESOURCE_NAME, $id);
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
