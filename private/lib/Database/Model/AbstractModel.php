<?php

namespace App\Database\Model;

use App\Database\Database;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;

abstract class AbstractModel
{
    public function save(): EntityManager
    {
        $db = Database::getInstance();
        try {
            $db->persist($this);
        } catch (ORMException $e) {
            throw new \RuntimeException($e->getMessage());
        }
        return $db;
    }

    /**
     * Gets the primary key, `id` column associated with the table.
     *
     * @return int
     */
    abstract public function getId(): int;
}