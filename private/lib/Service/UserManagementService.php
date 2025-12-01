<?php

declare(strict_types=1);

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\TemplateRepository;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Exception\UserException\NotFoundException;
use App\Service\Helper\CategoryHelper;
use App\Service\Helper\EntryHelper;
use App\Service\Helper\MediaHelper;
use App\Service\Helper\TemplateHelper;
use App\Service\Helper\UserHelper;
use App\Service\Helper\UserSetupHelper;
use App\Service\Helper\WidgetHelper;
use App\Service\Model\UserDecorator;
use App\Utility\Encryptor;

final readonly class UserManagementService
{
    private const string DEFAULT_PASSWORD_HASH_ALGORITHM = PASSWORD_ARGON2ID;

    public function __construct(
        private UserRepository $repository,
        private CategoryRepository $categoryRepository,
        private TemplateRepository $templateRepository,
        private UserHelper $userHelper,
        private CategoryHelper $categoryHelper,
        private EntryHelper $entryHelper,
        private WidgetHelper $widgetHelper,
        private TemplateHelper $templateHelper,
        private MediaHelper $mediaHelper,
        private UserExportService $userExportService,
    ) {}

    /**
     * Create a new user account for a logged in admin user.
     *
     * @return int User id
     */
    public function createUserForAdmin(int $loggedInUserId, string $username, string $password, string $email, int $privilegeLevel) : int
    {
        $loggedInUser = $this->userHelper->getUserById($loggedInUserId);

        if ($loggedInUser->getPrivilegeLevel() >= $privilegeLevel) {
            // 'admin' users are only allowed to create users with the 'user' privilege level
            throw InvalidOperationException::insufficientPrivileges($loggedInUser->getPrivilegeLevelAsString());
        }

        return $this->createUser($username, $password, $email, $privilegeLevel);
    }

    /**
     * Create a new user account.
     */
    public function createUser(string $username, string $password, string $email, int $privilegeLevel) : int
    {
        $user = $this->repository->findByUsername($username);

        if ($user instanceof User) {
            throw InvalidArgumentException::alreadyRegistered('username', $username);
        }

        $user = $this->repository->findByEmailAddress($email);

        if ($user instanceof User) {
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
            ->setEncryptionKey($protectedEncryptionKey)
        ;

        $this->repository->queue($user);
        $this->repository->save();

        $key = $encryptor->getKeyFromProtectedKey($protectedEncryptionKey, $password);
        $setup = new UserSetupHelper($user, $key, $this->repository, $this->categoryRepository, $this->templateRepository);
        $setup->setDefaults();

        return $user->getId();
    }

    public function getUserForAdmin(int $loggedInUserId, int $targetUserId) : UserDecorator
    {
        $user = $this->userHelper->getUserById($loggedInUserId);
        $targetUser = $this->userHelper->getUserById($targetUserId);

        $targetUserIsReadOnly = ! $this->userHasEditPrivilegesForTargetUser($user, $targetUser);

        $targetUserTotalEntries = $this->entryHelper->getEntryCountForUser($targetUser);
        $targetUserTotalCategories = $this->categoryHelper->getCategoryCountForUser($targetUser);
        $targetUserTotalTemplates = $this->templateHelper->getTemplateCountForUser($targetUser);

        return new UserDecorator(
            $targetUser,
            $targetUserIsReadOnly,
            $targetUserTotalCategories,
            $targetUserTotalEntries,
            $targetUserTotalTemplates,
        );
    }

    public function updateUserPrivilegesForAdmin(int $loggedInUserId, int $targetUserId, int $newPrivilegeLevel) : void
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

    public function deleteUserForAdmin(int $loggedInUserId, int $targetUserId) : void
    {
        $loggedInUser = $this->userHelper->getUserById($loggedInUserId);
        $targetUser = $this->userHelper->getUserById($targetUserId);

        $this->ensureUserHasUpdatePrivileges($loggedInUser, $targetUser);

        $this->deleteUser($targetUser);
    }

    public function deleteUser(User $targetUser) : void
    {
        $entries = $this->entryHelper->getAllEntriesForUser($targetUser);

        foreach ($entries as $entry) {
            $this->repository->remove($entry);
        }

        $templates = $this->templateHelper->getAllTemplatesForUser($targetUser);

        foreach ($templates as $template) {
            $this->repository->remove($template);
        }

        $categories = $this->categoryHelper->getAllCategoriesForUser($targetUser);

        foreach ($categories as $category) {
            $this->repository->remove($category);
        }

        $widgets = $this->widgetHelper->getAllWidgetsForUser($targetUser);

        foreach ($widgets as $widget) {
            $this->repository->remove($widget);
        }

        $files = $this->userExportService->getZipFileNamesForExportedEntriesByUser($targetUser->getId());

        foreach ($files as $file) {
            try {
                $this->userExportService->deleteExportedEntriesZipFile($targetUser->getId(), $file);
            } catch (NotFoundException) {
                continue;
            }
        }

        $this->mediaHelper->removeUserUploadDir($targetUser->getId());

        $this->repository->remove($targetUser);

        // Execute queued changes
        $this->repository->save();
    }

    private function ensureUserHasUpdatePrivileges(User $user, User $targetUser) : void
    {
        if ( ! $this->userHasEditPrivilegesForTargetUser($user, $targetUser)) {
            throw InvalidOperationException::insufficientPrivileges($user->getPrivilegeLevelAsString());
        }
    }

    private function userHasEditPrivilegesForTargetUser(User $user, User $targetUser) : bool
    {
        // Owners can edit admins and lower, and admins can edit users.
        return $user->getPrivilegeLevel() < $targetUser->getPrivilegeLevel();
    }
}
