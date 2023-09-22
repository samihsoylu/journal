<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\Framework\Core\TestOrm\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use SamihSoylu\Journal\Tests\Framework\Core\TestOrm\TestOrmInterface;

final class DoctrineTestOrm implements TestOrmInterface
{
    public function __construct(
       private readonly EntityManagerInterface $entityManager
    ) {}

    public function persist(object $object): void
    {
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }

    public function remove(object $object): void
    {
        $this->entityManager->remove($object);
        $this->entityManager->flush();
    }

    public function fetchOneAssoc(string $query, array $params = []): array
    {
        return $this->entityManager->getConnection()->fetchAssociative($query, $params);
    }

    public function fetchAllAssoc(string $query, array $params = []): array
    {
        return $this->entityManager->getConnection()->fetchAllAssociative($query, $params);
    }
}