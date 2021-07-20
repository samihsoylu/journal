<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\User;
use App\Database\Model\Category;

class CategoryRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    public const RESOURCE_NAME = Category::class;

    /**
     * Queries the database for a list of categories that were created by the provided user
     *
     * @param User $user
     * @return Category[]
     */
    public function findByUser(User $user): array
    {
        return $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(
                ['referencedUser' => $user],
                ['sortOrder' => 'ASC'],
            );
    }

    public function findByCategoryName(User $user, string $categoryName): Category
    {
        return $this->db->getRepository(self::RESOURCE_NAME)
            ->findOneBy([
                'referencedUser' => $user,
                 'name' => $categoryName,
                ]);
    }
}
