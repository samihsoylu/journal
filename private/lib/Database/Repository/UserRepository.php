<?php declare(strict_types=1);

namespace App\Database\Repository;

use App\Exception\UserException\NotFoundException;
use App\Database\Model\User;

class UserRepository extends AbstractRepository
{
    /**
     * @inheritdoc
     */
    protected const RESOURCE_NAME = User::class;

    /**
     * Retrieves a user from the users table via the provided username.
     *
     * @param string $username
     * @return User
     */
    public function getByUsername(string $username): ?User
    {
        $user = $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['username' => $username]);

        if (!isset($user[0])) {
            return null;
        }

        return $user[0];
    }

    public function getByEmailAddress(string $emailAddress): ?User
    {
        $user = $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['emailAddress' => $emailAddress]);

        if (!isset($user[0])) {
            return null;
        }

        return $user[0];
    }

}
