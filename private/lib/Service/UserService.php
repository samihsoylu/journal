<?php

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\NotFoundException;
use App\Service\Helpers\UserHelper;
use App\Utility\Registry;

class UserService
{
    private UserRepository $repository;
    private UserHelper $helper;
    private CategoryService $categoryService;
    private EntryService $entryService;

    public function __construct()
    {
        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);

        /** @var UserHelper $helper */
        $helper = Registry::get(UserHelper::class);

        /** @var CategoryService $categoryService */
        $categoryService = Registry::get(CategoryService::class);

        /** @var EntryService $entryService */
        $entryService = Registry::get(EntryService::class);

        $this->repository = $repository;
        $this->helper = $helper;
        $this->categoryService = $categoryService;
        $this->entryService = $entryService;
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

    public function getHelper(): UserHelper
    {
        return $this->helper;
    }

    public function getCategoryService(): CategoryService
    {
        return $this->categoryService;
    }

    public function getEntryService(): EntryService
    {
        return $this->entryService;
    }
}