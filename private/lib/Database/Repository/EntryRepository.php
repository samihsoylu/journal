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

    /**
     * @return Entry[]
     */
    public function getEntriesBySearchQueryLimitCategoryStartEndDateAndOffset(
        int $userId,
        ?string $search,
        ?int $categoryId,
        ?int $startCreatedDate,
        ?int $endCreatedDate,
        ?int $offset,
        ?int $limit
    ): array {
        $qb = $this->db->createQueryBuilder();

        $qb->select('e')
            ->from(self::RESOURCE_NAME, 'e')
            ->where('e.referencedUser = :userId')
            ->orderBy('e.createdTimestamp', 'DESC')
            ->setParameter('userId', $userId);

        if ($categoryId !== null) {
            $qb->andWhere('e.referencedCategory = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        if ($startCreatedDate !== null && $endCreatedDate !== null) {
            // adds 23h59m so that entires after 00:00 are also included.
            $endCreatedDate += 86340;

            $qb->andWhere('e.createdTimestamp BETWEEN :startTime AND :endTime')
                ->setParameter('startTime', $startCreatedDate)
                ->setParameter('endTime', $endCreatedDate + 86340);
        }

        if ($search !== null) {
            $qb->andWhere($qb->expr()->like('e.title', ':search'))
                ->setParameter('search', "%{$search}%");
        }

        if ($limit !== null) {
            $offset = $offset ?? 0;

            $qb->setFirstResult($offset)
                ->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}
