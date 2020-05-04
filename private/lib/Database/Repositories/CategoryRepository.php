<?php declare(strict_types=1);

namespace App\Database\Repositories;

use App\Database\Exception\NotFoundException;
use App\Database\Models\Category;

class CategoryRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = Category::class;

    /**
     * Retrieves a category from the category table via the provided category name.
     *
     * @param string $categoryName
     *
     * @return Category
     */
    public function getByName(string $categoryName): Category
    {
        $category = $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['categoryName' => $categoryName]);

        if (!isset($category[0])) {
            throw NotFoundException::entityNameNotFound(self::RESOURCE_NAME, $categoryName);
        }

        return $category[0];
    }
}
