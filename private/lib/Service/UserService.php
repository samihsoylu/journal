<?php

namespace App\Service;

use App\Database\Model\User;
use App\Database\Model\User as UserModel;
use App\Database\Repository\UserRepository;
use App\Decorator\UserDecorator;
use App\Exception\UserException\InvalidArgumentException;
use App\Logic\UserLogic;
use App\Logic\CategoryLogic;
use App\Logic\EntryLogic;
use App\Utility\Registry;

class UserService
{
    private UserRepository $repository;
    private UserLogic $userLogic;
    private CategoryLogic $categoryLogic;
    private EntryLogic $entryLogic;

    public function __construct()
    {
        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);

        $this->repository    = $repository;
        $this->userLogic     = new UserLogic();
        $this->categoryLogic = new CategoryLogic();
        $this->entryLogic    = new EntryLogic();
    }

    /**
     * Get all registered users
     *
     * @return UserModel[]
     */
    public function getAllUsers(): array
    {
        return $this->userLogic->getAllUsers();
    }

    /**
     * Finds a user based on a provided user id
     *
     * @return UserModel
     */
    public function getUserById(int $userId): UserModel
    {
        return $this->userLogic->getUserById($userId);
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

        $user = new UserModel();
        $user->setUsername($username)
            ->setPassword($encryptedPassword)
            ->setEmailAddress($email)
            ->setPrivilegeLevel(UserModel::PRIVILEGE_LEVEL_USER)
            ->setEncryptionKey($protectedEncryptionKey);

        $this->repository->queue($user);
        $this->repository->save();

        return $user->getId();
    }

    public function getUserForLoggedInUser(int $loggedInUserId, int $requestedUserId): UserDecorator
    {
        $user       = $this->getUserById($loggedInUserId);
        $targetUser = $this->getUserById($requestedUserId);


        $targetUserIsReadOnly = !$this->userHasEditPrivilegesForTargetUser($user, $targetUser);

        $targetUserTotalEntries = $this->entryLogic->getEntryCountForUser($targetUser);
        $targetUserTotalCategories = $this->categoryLogic->getCategoryCountForUser($targetUser);

        return new UserDecorator(
            $targetUser,
            $targetUserIsReadOnly,
            $targetUserTotalCategories,
            $targetUserTotalEntries
        );
    }

    private function userHasEditPrivilegesForTargetUser(UserModel $user, UserModel $targetUser): bool
    {
        // Owners can edit admins and lower, and admins can edit users.
        return ($user->getPrivilegeLevel() < $targetUser->getPrivilegeLevel());
    }
}
