<?php

namespace App\Database\Repository;

use App\Database\Database;

abstract class AbstractRepository
{
    public static function getAll(): array
    {
        $db = Database::getInstance();
        $resource = $db->getRepository(static::RESOURCE_NAME);
        return $resource->findAll();
    }

    public static function getById($id): Object
    {
        $db = Database::getInstance();
        $resource = $db->find(static::RESOURCE_NAME, $id);


        return $resource;
    }
}