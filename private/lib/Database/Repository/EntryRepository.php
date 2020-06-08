<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\Entry;

class EntryRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = Entry::class;

    public function findByCategoryId(int $userId, int $categoryId)
    {

    }

    public function findByTimeframe(int $userId, int $startTime, int $endTime)
    {

    }

    public function findByCategoryAndTimeFrame(
        int $userId,
        int $categoryId,
        int $startTime,
        int $endTime,
        int $page,
        int $limit
    ): array
    {
        $qb = $this->db->createQueryBuilder();
        $qb->add('select', 'e')
            ->add('from', 'Entries e')
            ->add('orderBy', 'e.createdTimestamp DESC')
            ->setFirstResult($page)
            ->setMaxResults($limit);

        $qb->where('e.createdTimestamp BETWEEN :startTime AND :endTime')
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        return $qb->getQuery()->getArrayResult();
    }
}
