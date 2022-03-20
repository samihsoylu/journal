<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\Template;
use App\Database\Model\User;

/**
 * @method Template[] getAll()
 * @method Template|null getById(int $id)
 */
class TemplateRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    public const RESOURCE_NAME = Template::class;

    /**
     * Queries the database for a list of templates that were created by the provided user
     *
     * @param User $user
     * @return Template[]
     */
    public function findByUser(User $user): array
    {
        return $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['referencedUser' => $user]);
    }

    /**
     * Queries the database for all templates that are linked to the specified category
     *
     * @return Template[]
     */
    public function findByUserIdAndCategoryId(int $userId, int $categoryId): array
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('e')
            ->from(self::RESOURCE_NAME, 'e')
            ->where('e.referencedCategory = :categoryId AND e.referencedUser = :userId')
            ->setParameter('categoryId', $categoryId)
            ->setParameter('userId', $userId);

        return $qb->getQuery()->getResult();
    }
}
