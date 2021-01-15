<?php

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Service\Model\UserDecorator;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Service\Helper\CategoryHelper;
use App\Service\Helper\EntryHelper;
use App\Service\Helper\UserHelper;
use App\Utility\Encryptor;
use App\Utility\Registry;

class UserService
{
    private UserRepository $repository;
    private UserHelper $userHelper;
    private CategoryHelper $categoryHelper;
    private EntryHelper $entryHelper;

    public function __construct()
    {
        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);

        $this->repository     = $repository;
        $this->userHelper     = new UserHelper();
        $this->categoryHelper = new CategoryHelper();
        $this->entryHelper    = new EntryHelper();
    }

    /**
     * Get all registered users
     *
     * @return User[]
     */
    public function getAllUsers(): array
    {
        return $this->userHelper->getAllUsers();
    }

    /**
     * Create a new user account
     *
     * @return int User id
     */
    public function register(int $loggedInUserId, string $username, string $password, string $email, int $privilegeLevel): int
    {
        $loggedInUser = $this->userHelper->getUserById($loggedInUserId);

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

        $user = new User();
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
        $user       = $this->userHelper->getUserById($loggedInUserId);
        $targetUser = $this->userHelper->getUserById($targetUserId);

        $targetUserIsReadOnly = !$this->userHasEditPrivilegesForTargetUser($user, $targetUser);

        $targetUserTotalEntries = $this->entryHelper->getEntryCountForUser($targetUser);
        $targetUserTotalCategories = $this->categoryHelper->getCategoryCountForUser($targetUser);

        return new UserDecorator(
            $targetUser,
            $targetUserIsReadOnly,
            $targetUserTotalCategories,
            $targetUserTotalEntries
        );
    }

    public function updateUserPrivileges(int $loggedInUserId, int $targetUserId, int $newPrivilegeLevel): void
    {
        $loggedInUser = $this->userHelper->getUserById($loggedInUserId);
        $targetUser = $this->userHelper->getUserById($targetUserId);

        $this->ensureUserHasUpdatePrivileges($loggedInUser, $targetUser);

        if ($newPrivilegeLevel <= $loggedInUser->getPrivilegeLevel()) {
            // logged in user may not give the same privileges or higher to the target user
            throw InvalidOperationException::insufficientPrivileges($loggedInUser->getPrivilegeLevelAsString());
        }

        $targetUser->setPrivilegeLevel($newPrivilegeLevel);

        $this->repository->queue($targetUser);
        $this->repository->save();
    }

    public function deleteUser(int $loggedInUserId, int $targetUserId): void
    {
        $loggedInUser = $this->userHelper->getUserById($loggedInUserId);
        $targetUser = $this->userHelper->getUserById($targetUserId);

        $this->ensureUserHasUpdatePrivileges($loggedInUser, $targetUser);

        $entries = $this->entryHelper->getAllEntriesForUser($targetUser);
        foreach ($entries as $entry) {
            $this->repository->remove($entry);
        }

        $categories = $this->categoryHelper->getAllCategoriesForUser($targetUser);
        foreach ($categories as $category) {
            $this->repository->remove($category);
        }

        $this->repository->remove($targetUser);

        // Execute queued changes
        $this->repository->save();
    }

    private function ensureUserHasUpdatePrivileges(User $user, User $targetUser): void
    {
        if (!$this->userHasEditPrivilegesForTargetUser($user, $targetUser)) {
            throw InvalidOperationException::insufficientPrivileges($user->getPrivilegeLevelAsString());
        }
    }

    private function userHasEditPrivilegesForTargetUser(User $user, User $targetUser): bool
    {
        // Owners can edit admins and lower, and admins can edit users.
        return ($user->getPrivilegeLevel() < $targetUser->getPrivilegeLevel());
    }
}
