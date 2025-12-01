<?php

declare(strict_types=1);

namespace Tests\TestFramework\TestOrm\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Tests\TestFramework\TestOrm\TestOrmInterface;

/**
 * Doctrine implementation of TestOrmInterface.
 *
 * Wraps Doctrine's EntityManager to provide:
 * - Raw SQL query execution via DBAL Connection
 * - Entity persistence with automatic flush
 */
final readonly class DoctrineTestOrm implements TestOrmInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function fetchOneAssoc(string $sql, array $params = []) : ?array
    {
        $result = $this->entityManager->getConnection()->fetchAssociative($sql, $params);

        return $result !== false ? $result : null;
    }

    public function fetchAllAssoc(string $sql, array $params = []) : array
    {
        return $this->entityManager->getConnection()->fetchAllAssociative($sql, $params);
    }

    public function save(object ...$entities) : void
    {
        foreach ($entities as $entity) {
            $this->entityManager->persist($entity);
        }

        $this->entityManager->flush();
    }
}
