<?php

namespace App\Service;

use App\Database\Model\User;
use App\Database\Model\User as UserModel;
use App\Database\Repository\UserRepository;
use App\Decorator\UserDecorator;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Logic\UserLogic;
use App\Logic\CategoryLogic;
use App\Logic\EntryLogic;
use App\Utility\Encryptor;
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
     * Create a new user account
     *
     * @return int User id
     */
    public function register(int $loggedInUserId, string $username, string $password, string $email, int $privilegeLevel): int
    {
        $loggedInUser = $this->userLogic->getUserById($loggedInUserId);

        if ($loggedInUser->getPrivilegeLevel() >= $privilegeLevel) {
            // 'admin' users are only allowed to create users with the 'user' privilege level
            throw InvalidOperationException::insufficientPrivileges($loggedInUser->getPrivilegeLevelAsString());
        }

        $user = $this->repository->findByUsername($username);
        if ($user !== null) {
            throw InvalidArgumentException::alreadyRegistered('username', $username);
        }

        $user = $this->repository->findByEmailAddress($email);
        if ($user !== null) {
            throw InvalidArgumentException::alreadyRegistered('email', $email);
        }

        $encryptedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $encryptor = new Encryptor();
        $protectedEncryptionKey = $encryptor->generateProtectedEncryptionKey($password);

        $user = new UserModel();
        $user->setUsername($username)
            ->setPassword($encryptedPassword)
            ->setEmailAddress($email)
            ->setPrivilegeLevel($privilegeLevel)
            ->setEncryptionKey($protectedEncryptionKey);

        $this->repository->queue($user);
        $this->repository->save();

        return $user->getId();
    }

    public function getUserForLoggedInUser(int $loggedInUserId, int $targetUserId): UserDecorator
    {
        $user       = $this->userLogic->getUserById($loggedInUserId);
        $targetUser = $this->userLogic->getUserById($targetUserId);


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

    public function deleteUser(int $loggedInUserId, int $targetUserId): void
    {
        $loggedInUser = $this->userLogic->getUserById($loggedInUserId);
        $targetUser = $this->userLogic->getUserById($targetUserId);

        $this->ensureUserHasUpdatePrivileges($loggedInUser, $targetUser);

        $entries = $this->entryLogic->getAllEntriesForUser($targetUser);
        foreach ($entries as $entry) {
            $this->repository->remove($entry);
        }

        $categories = $this->categoryLogic->getAllCategoriesForUser($targetUser);
        foreach ($categories as $category) {
            $this->repository->remove($category);
        }

        $this->repository->remove($targetUser);

        // Execute queued changes
        $this->repository->save();
    }

    private function ensureUserHasUpdatePrivileges(UserModel $user, UserModel $targetUser): void
    {
        if (!$this->userHasEditPrivilegesForTargetUser($user, $targetUser)) {
            throw InvalidOperationException::insufficientPrivileges($user->getPrivilegeLevelAsString());
        }
    }

    private function userHasEditPrivilegesForTargetUser(UserModel $user, UserModel $targetUser): bool
    {
        // Owners can edit admins and lower, and admins can edit users.
        return ($user->getPrivilegeLevel() < $targetUser->getPrivilegeLevel());
    }
}
