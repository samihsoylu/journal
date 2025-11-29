<?php

declare(strict_types=1);

namespace App\Database\Repository;

use App\Database\Model\User;

/**
 * @method User[] getAll()
 * @method null|User getById(int $id)
 */
final class UserRepository extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public const RESOURCE_NAME = User::class;

    /**
     * Retrieves a user from the users table via the provided username.
     */
    public function findByUsername(string $username) : ?User
    {
        return $this->db->getRepository(self::RESOURCE_NAME)
            ->findOneBy(['username' => $username])
        ;
    }

    public function findByEmailAddress(string $emailAddress) : ?User
    {
        return $this->db->getRepository(self::RESOURCE_NAME)
            ->findOneBy(['emailAddress' => $emailAddress])
        ;
    }
}
