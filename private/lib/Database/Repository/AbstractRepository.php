<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Database;
use App\Exception\UserException\NotFoundException;
use App\Database\Model\ModelInterface;
use \Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use \Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\TransactionRequiredException;

abstract class AbstractRepository
{
    /** @var EntityManager $db database instance */
    protected EntityManager $db;

    /**
     * @var string RESOURCE_NAME name of the database model (name of table)
     */
    public const RESOURCE_NAME = '';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retrieves all entries from the database table of RESOURCE_NAME
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->db->getRepository(static::RESOURCE_NAME)->findAll();
    }

    /**
     * Retrieves a single row from a table by the provided record id, and return found record as a Model.
     *
     * @param int $id
     *
     * @return object
     * @throws NotFoundException|TransactionRequiredException|OptimisticLockException|ORMException
     */
    public function getById(int $id): ?object
    {
        return $this->db->find(static::RESOURCE_NAME, $id);
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
     * @throws ORMException|OptimisticLockException
     */
    public function save(): void
    {
        $this->db->flush();
    }
}
