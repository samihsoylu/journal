<?php

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Service\Helper\TemplateHelper;
use App\Service\Helper\UserSetupHelper;
use App\Service\Helper\WidgetHelper;
use App\Service\Model\UserDecorator;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Service\Helper\CategoryHelper;
use App\Service\Helper\EntryHelper;
use App\Service\Helper\UserHelper;
use App\Utility\Command\Command;
use App\Utility\Command\Process;
use App\Utility\Encryptor;
use App\Utility\Lock\Lock;
use App\Utility\Lock\LockName;
use App\Utility\Registry;
use App\Utility\UserSession;
use Defuse\Crypto\Key;

class UserService
{
    private UserRepository $repository;
    private UserHelper $userHelper;
    private CategoryHelper $categoryHelper;
    private EntryHelper $entryHelper;
    private WidgetHelper $widgetHelper;
    private TemplateHelper $templateHelper;

    private const DEFAULT_PASSWORD_HASH_ALGORITHM = PASSWORD_ARGON2ID;

    public function __construct()
    {
        /** @var UserRepository $repository */
        $repository = Registry::get(UserRepository::class);

        $this->repository     = $repository;
        $this->userHelper     = new UserHelper();
        $this->categoryHelper = new CategoryHelper();
        $this->entryHelper    = new EntryHelper();
        $this->templateHelper = new TemplateHelper();
        $this->widgetHelper   = new WidgetHelper();
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
     * Create a new user account for a logged in user
     *
     * @return int User id
     */
    public function createUserForAdmin(int $loggedInUserId, string $username, string $password, string $email, int $privilegeLevel): int
    {
        $loggedInUser = $this->userHelper->getUserById($loggedInUserId);

        if ($loggedInUser->getPrivilegeLevel() >= $privilegeLevel) {
            // 'admin' users are only allowed to create users with the 'user' privilege level
            throw InvalidOperationException::insufficientPrivileges($loggedInUser->getPrivilegeLevelAsString());
        }

        return $this->createUser($username, $password, $email, $privilegeLevel);
    }

    /**
     * Create a new user account
     *
     * @return int
     */
    public function createUser(string $username, string $password, string $email, int $privilegeLevel): int
    {
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
            ->setEncryptionKey($protectedEncryptionKey)
            ->save();

        $key = $encryptor->getKeyFromProtectedKey($protectedEncryptionKey, $password);
        $setup = new UserSetupHelper($user, $key);
        $setup->setDefaults();

        return $user->getId();
    }

    public function getUserForAdmin(int $loggedInUserId, int $targetUserId): UserDecorator
    {
        $user       = $this->userHelper->getUserById($loggedInUserId);
        $targetUser = $this->userHelper->getUserById($targetUserId);

        $targetUserIsReadOnly = !$this->userHasEditPrivilegesForTargetUser($user, $targetUser);

        $targetUserTotalEntries = $this->entryHelper->getEntryCountForUser($targetUser);
        $targetUserTotalCategories = $this->categoryHelper->getCategoryCountForUser($targetUser);
        $targetUserTotalTemplates = $this->templateHelper->getTemplateCountForUser($targetUser);

        return new UserDecorator(
            $targetUser,
            $targetUserIsReadOnly,
            $targetUserTotalCategories,
            $targetUserTotalEntries,
            $targetUserTotalTemplates
        );
    }

    public function updateUserPrivilegesForAdmin(int $loggedInUserId, int $targetUserId, int $newPrivilegeLevel): void
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

        $this->deleteUser($targetUser);
    }

    /**
     * Deletes user for logged in user (account page)
     *
     * @return void
     */
    public function deleteUserForUser(string $currentPassword, int $userId): void
    {
        $user = $this->userHelper->getUserById($userId);

        if ($user->getPrivilegeLevel() === User::PRIVILEGE_LEVEL_OWNER) {
            throw InvalidOperationException::insufficientPrivileges($user->getPrivilegeLevelAsString());
        }

        if (!password_verify($currentPassword, $user->getPassword())) {
            throw InvalidArgumentException::incorrectPassword();
        }

        $this->deleteUser($user);

        UserSession::destroy();
    }

    public function deleteUser(User $targetUser): void
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
        $user->save();
    }

    public function getUser(int $loggedInUserId): User
    {
        return $this->userHelper->getUserById($loggedInUserId);
    }

    public function changeUserEmail(int $userId, string $newEmailAddress): void
    {
        $user = $this->userHelper->getUserById($userId);
        $user->setEmailAddress($newEmailAddress);
        $user->save();
    }

    /**
     * @param int $userId
     * @param Key $encryptionKey used for decrypting entry contents
     */
    public function exportUserEntriesToMarkdown(int $userId, Key $encryptionKey): int
    {
        $user = $this->userHelper->getUserById($userId);
        $exportScriptFilePath = SCRIPTS_PATH . '/ExportAllEntriesForUser.php';

        $this->ensureExportIsNotAlreadyRunning($user->getId(), $user->getUsername());
        $this->ensureScriptExists($exportScriptFilePath);

        $command = new Command([
            '/usr/bin/php', $exportScriptFilePath, $userId, $user->getUsername(), $encryptionKey->saveToAsciiSafeString()
        ]);

        $process = Process::start(
            $command,
            BASE_PATH . "/private/cache/export/log/{$user->getUsername()}.log"
        );

        return $process->getId();
    }

    private function ensureExportIsNotAlreadyRunning(int $userId, string $username): void
    {
        $lockName = LockName::create($userId, $username, LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER);
        if (Lock::exists($lockName)) {
            throw InvalidOperationException::actionIsAlreadyRunning('exporting entries');
        }
    }

    private function ensureScriptExists(string $scriptPath): void
    {
        if (!file_exists($scriptPath)) {
            throw new \LogicException("Script in path: {$scriptPath} does not exist");
        }
    }

    public function getZipFileNamesForExportedEntriesByUser(int $userId): array
    {
        $user = $this->userHelper->getUserById($userId);

        /** @see EntryExporter::zipAllEntries() */
        $exportedFiles = glob(EXPORT_CACHE_PATH . "/{$user->getUsername()}__*.zip");

        return array_map(static function(string $file) {
            return basename($file);
        }, $exportedFiles);
    }
}
