<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\Entry;
use App\Database\Model\User;

class EntryRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = Entry::class;

    /**
     * @return Entry[]
     */
    public function findByUser(User $user): array
    {
        return $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['referencedUser' => $user]);
    }

    /**
     * @return Entry[]
     */
    public function findByUserIdAndCategoryId(int $userId, int $categoryId): array
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('e')
            ->from(self::RESOURCE_NAME, 'e')
            ->where('e.referencedCategory = :categoryId AND e.referencedUser = :userId')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('userId', $userId);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByUserIdStartTimeAndEndTime(int $userId, int $startTime, int $endTime)
    {
        $queryBuilder = $this->db->createQueryBuilder();

        $queryBuilder->select('e')
            ->from(self::RESOURCE_NAME, 'e')
            ->where('e.referencedUser = :userId AND e.createdTimestamp BETWEEN :startTime AND :endTime')
            ->setParameter('userId', $userId)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByCategoryAndTimeFrame(
        int $userId,
        int $categoryId,
        int $startTime,
        int $endTime,
        int $offset,
        int $limit
    ): array {
        $qb = $this->db->createQueryBuilder();

        $qb->add('select', 'e')
            ->add('from', 'Entries e')
            ->add('orderBy', 'e.createdTimestamp DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $qb->where('e.categoryId = :categoryId AND e.userId = :userId')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('userId', $userId);

        $qb->where('e.createdTimestamp BETWEEN :startTime AND :endTime')
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        return $qb->getQuery()->getArrayResult();
    }
}
