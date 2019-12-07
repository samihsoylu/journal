<?php

namespace App\Database\Repository;

use App\Database\Database;
use App\Database\Model\Category;

class CategoryRepository extends AbstractRepository
{
    protected const RESOURCE_NAME = Category::class;

    public static function getByName($categoryName): Category
    {
        $db = Database::getInstance();
        $category = $db->getRepository(self::RESOURCE_NAME)
            ->findBy(['categoryName' => $categoryName]);

        return $category[0];
    }
}