<?php

declare(strict_types=1);

namespace Tests\TestFramework\TestOrm\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Tests\TestFramework\TestOrm\TestOrm;

/**
 * Doctrine implementation of TestOrmInterface.
 *
 * Wraps Doctrine's EntityManager to provide:
 * - Raw SQL query execution via DBAL Connection
 * - Entity persistence with automatic flush
 */
final readonly class DoctrineTestOrm implements TestOrm
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function fetchOneAssoc(string $sql, array $params = []) : ?array
    {
        $connection = $this->entityManager->getConnection();

        return $connection->fetchAssociative($sql, $params);
    }

    public function fetchAllAssoc(string $sql, array $params = []) : array
    {
        $connection = $this->entityManager->getConnection();

        return $connection->fetchAllAssociative($sql, $params);
    }

    public function save(object ...$entities) : void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }
}
