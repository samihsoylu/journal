<?php

namespace App\Service;

use App\Database\Model\Category;
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
use App\Utility\UserSession;

class UserService
{
    private UserRepository $repository;
    private UserHelper $userHelper;
    private CategoryHelper $categoryHelper;
    private EntryHelper $entryHelper;

    private const DEFAULT_PASSWORD_HASH_ALGORITHM = PASSWORD_ARGON2ID;

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

        $encryptedPassword = password_hash($password, self::DEFAULT_PASSWORD_HASH_ALGORITHM);

        $encryptor = new Encryptor();
        $protectedEncryptionKey = $encryptor->generateProtectedKey($password);

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

    public function deleteUserForAdmin(int $loggedInUserId, int $targetUserId): void
    {
        $loggedInUser = $this->userHelper->getUserById($loggedInUserId);
        $targetUser = $this->userHelper->getUserById($targetUserId);

        $this->ensureUserHasUpdatePrivileges($loggedInUser, $targetUser);

        $this->deleteUserFromDatabase($targetUser);
    }

    public function deleteUser(string $currentPassword, int $userId): void
    {
        $user = $this->userHelper->getUserById($userId);

        if ($user->getPrivilegeLevel() === User::PRIVILEGE_LEVEL_OWNER) {
            throw InvalidOperationException::insufficientPrivileges($user->getPrivilegeLevelAsString());
        }

        if (!password_verify($currentPassword, $user->getPassword())) {
            throw InvalidArgumentException::incorrectPassword();
        }

        $this->deleteUserFromDatabase($user);

        UserSession::destroy();
    }

    private function deleteUserFromDatabase(User $targetUser): void
    {
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

    public function createDefaultCategoriesForUser(int $userId): void
    {
        $user = $this->userHelper->getUserById($userId);

        $personal = new Category();
        $personal->setName('Personal');
        $personal->setDescription('Stories about your passions and ambitions');
        $personal->setReferencedUser($user);
        $this->repository->queue($personal);

        $diet = new Category();
        $diet->setName('Diet');
        $diet->setDescription('Food journaling for reaching healthy eating goals');
        $diet->setReferencedUser($user);
        $this->repository->queue($diet);

        $dreams = new Category();
        $dreams->setName('Dreams');
        $dreams->setDescription('Recording dream experiences allow you to start analyzing what your dreams mean');
        $dreams->setReferencedUser($user);
        $this->repository->queue($dreams);

        $work = new Category();
        $work->setName('Work');
        $work->setDescription('Meeting notes, deadlines, countless other bits of information that are best stored here instead of your brain');
        $work->setReferencedUser($user);
        $this->repository->queue($work);

        $gifts = new Category();
        $gifts->setName('Gifts');
        $gifts->setDescription('Gifts you have received or given to friends and family');
        $gifts->setReferencedUser($user);
        $this->repository->queue($gifts);

        $this->repository->save();
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->userHelper->getUserById($userId);
        if (!password_verify($currentPassword, $user->getPassword())) {
            throw InvalidArgumentException::incorrectPassword();
        }

        $newEncryptedPassword = password_hash($newPassword, self::DEFAULT_PASSWORD_HASH_ALGORITHM);
        $user->setPassword($newEncryptedPassword);

        $encryptor = new Encryptor();
        $newEncryptedKey = $encryptor->changePassword($user->getEncryptionKey(), $currentPassword, $newPassword);
        $user->setEncryptionKey($newEncryptedKey);

        $this->repository->queue($user);
        $this->repository->save();
    }

    public function getUser(int $loggedInUserId): User
    {
        return $this->userHelper->getUserById($loggedInUserId);
    }

    public function changeUserEmail(int $userId, string $newEmailAddress): void
    {
        $user = $this->userHelper->getUserById($userId);
        $user->setEmailAddress($newEmailAddress);

        $this->repository->queue($user);
        $this->repository->save();
    }
}
