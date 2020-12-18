<?php

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\NotFoundException;
use App\Service\Helpers\UserHelper;
use App\Utility\Encryptor;
use App\Utility\Registry;

class UserService
{
    private UserRepository $repository;
    private UserHelper $helper;
    private CategoryService $categoryService;
    private EntryService $entryService;
    private Encryptor $encryptor;

    public function __construct()
    {
        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);
        $this->repository = $repository;

        $this->helper          = new UserHelper();
        $this->categoryService = new CategoryService();
        $this->entryService    = new EntryService();
        $this->encryptor       = new Encryptor();
    }

    /**
     * Get all registered users from the database
     *
     * @return User[]
     */
    public function getAllUsers(): array
    {
        return $this->repository->getAll();
    }

    /**
     * Finds a user based on a provided user id
     *
     * @return User
     */
    public function getUserById(int $userId): User
    {
        /** @var User $user */
        $user = $this->repository->getById($userId);
        if ($user === null) {
            throw NotFoundException::entityIdNotFound('User', $userId);
        }

        return $user;
    }

    /**
     * Create a new user account
     *
     * @return int User id
     */
    public function register(string $username, string $password, string $email): int
    {
        $user = $this->repository->findByUsername($username);
        if ($user !== null) {
            throw InvalidArgumentException::alreadyRegistered('username', $username);
        }

        $user = $this->repository->findByEmailAddress($email);
        if ($user !== null) {
            throw InvalidArgumentException::alreadyRegistered('email', $email);
        }

        $encryptedPassword      = password_hash($password, PASSWORD_ARGON2ID);
        $protectedEncryptionKey = $this->encryptor->generateProtectedEncryptionKey($password);

        $user = new User();
        $user->setUsername($username)
            ->setPassword($encryptedPassword)
            ->setEmailAddress($email)
            ->setPrivilegeLevel(User::PRIVILEGE_LEVEL_USER)
            ->setEncryptionKey($protectedEncryptionKey);

        $this->repository->queue($user);
        $this->repository->save();

        return $user->getId();
    }

    /**
     * Generate a struct for the single user view page
     *
     * @return array
     */
    public function getUserViewStruct(int $loggedInUserId, int $requestedUserId): array
    {
        $targetUser = $this->getUserById($requestedUserId);
        $user       = $this->getUserById($loggedInUserId);

        return [
            'user'            => $targetUser,
            'isReadOnly'      => !$this->helper->userHasEditPrivilegesForTargetUser($user, $targetUser),
            'totalEntries'    => $this->entryService->getEntryCountForUser($targetUser),
            'totalCategories' => $this->categoryService->getCategoryCountForUser($targetUser),
        ];
    }
}