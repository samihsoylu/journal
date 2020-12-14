<?php

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\NotFoundException;
use App\Utility\Registry;

class UserService
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = Registry::get(UserRepository::class);
    }

    /**
     * @return User[]
     */
    public function getAllUsers(): array
    {
        return $this->repository->getAll();
    }

    public function getUser(int $userId): User
    {
        /** @var User $user */
        $user = $this->repository->getById($userId);
        if ($user === null) {
            throw NotFoundException::entityIdNotFound('User', $userId);
        }

        return $user;
    }
}