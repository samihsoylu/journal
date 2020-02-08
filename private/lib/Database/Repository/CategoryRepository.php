<?php

namespace App\Database\Repository;

use App\Database\Model\Category;

class CategoryRepository extends AbstractRepository
{
    protected const RESOURCE_NAME = Category::class;

    public function getByName($categoryName): Category
    {
        $category = $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['categoryName' => $categoryName]);

        return $category[0];
    }
}