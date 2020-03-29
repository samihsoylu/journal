<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Exception\NotFoundException;
use App\Database\Model\Category;

class CategoryRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = Category::class;

    /**
     * Retrieves a category from the category table via the provided category name.
     *
     * @param $categoryName
     *
     * @return Category
     */
    public function getByName($categoryName): Category
    {
        $category = $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['categoryName' => $categoryName]);

        if (!$category[0]) {
            throw NotFoundException::entityNameNotFound(self::RESOURCE_NAME, $categoryName);
        }

        return $category[0];
    }
}
