<?php declare(strict_types=1);

namespace App\Database\Repositories;

use App\Database\Exception\NotFoundException;
use App\Database\Models\User;

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
    public function getByUsername(string $username): User
    {
        $user = $this->db->getRepository(self::RESOURCE_NAME)
            ->findBy(['username' => $username]);

        if (!isset($user[0])) {
            throw NotFoundException::entityNameNotFound(self::RESOURCE_NAME, $username);
        }

        return $user[0];
    }
}
