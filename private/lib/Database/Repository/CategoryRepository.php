<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\User;
use App\Exception\UserException\NotFoundException;
use App\Database\Model\Category;

class CategoryRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = Category::class;

    /**
     * Queries the database, retrieves a category from the category table by the provided category name.
     *
     * @param string $categoryName
     *
     * @return Category
     */
    public function getByName(string $categoryName): Category
    {
        $category = $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['name' => $categoryName]);

        if (!isset($category[0])) {
            throw NotFoundException::entityNameNotFound(self::RESOURCE_NAME, $categoryName);
        }

        return $category[0];
    }

    /**
     * Queries the database for a list of categories that were created by the provided user
     *
     * @param User $user
     * @return Category[]
     */
    public function getAllCategoriesForUser(User $user): array
    {
        $categories = $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['referencedUser' => $user]);

        if (!isset($categories[0])) {
            return [];
        }

        return $categories;
    }
}
