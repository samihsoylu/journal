<?php declare(strict_types=1);

namespace App\Service\Helper;

use App\Database\Model\User as UserModel;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\NotFoundException;
use App\Utility\Registry;

class UserHelper
{
    private UserRepository $repository;

    public function __construct()
    {
        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);
        $this->repository = $repository;
    }

    /**
     * Get all registered users from the database
     *
     * @return UserModel[]
     */
    public function getAllUsers(): array
    {
        return $this->repository->getAll();
    }

    /**
     * Finds a user based on a provided user id
     *
     * @return UserModel
     */
    public function getUserById(int $userId): UserModel
    {
        /** @var UserModel $user */
        $user = $this->repository->getById($userId);
        if ($user === null) {
            throw NotFoundException::entityIdNotFound('User', $userId);
        }

        return $user;
    }
}
