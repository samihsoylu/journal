<?php

namespace App\Database\Repository;

use App\Database\Model\User;
use App\Database\Model\Widget;

/**
 * @method Widget[] getAll()
 * @method Widget|null getById(int $id)
 */
class WidgetRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    public const RESOURCE_NAME = Widget::class;

    /**
     * Queries the database for a list of categories that were created by the provided user
     *
     * @param User $user
     * @return Widget[]
     */
    public function findByUser(User $user): array
    {
        return $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['referencedUser' => $user]);
    }
}
