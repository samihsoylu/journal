<?php

declare(strict_types=1);

namespace App\Service;

use App\Database\Model\User;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Service\Helper\UserHelper;
use App\Utility\Encryptor;
use App\Utility\UserSession;

final readonly class UserService
{
    private const string DEFAULT_PASSWORD_HASH_ALGORITHM = PASSWORD_ARGON2ID;

    public function __construct(
        private UserRepository $repository,
        private UserHelper $userHelper,
        private UserSession $userSession,
        private UserManagementService $userManagementService,
    ) {}

    /**
     * Get all registered users.
     *
     * @return User[]
     */
    public function getAllUsers() : array
    {
        return $this->userHelper->getAllUsers();
    }

    public function getUser(int $loggedInUserId) : User
    {
        return $this->userHelper->getUserById($loggedInUserId);
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword) : void
    {
        $user = $this->userHelper->getUserById($userId);

        if ( ! password_verify($currentPassword, $user->getPassword())) {
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

    public function changeUserEmail(int $userId, string $newEmailAddress) : void
    {
        $user = $this->userHelper->getUserById($userId);
        $user->setEmailAddress($newEmailAddress);

        $this->repository->queue($user);
        $this->repository->save();
    }

    public function setDateTimeZoneForUser(int $getUserId, string $timezone) : void
    {
        $user = $this->userHelper->getUserById($getUserId);
        $user->setTimezone($timezone);

        $this->repository->queue($user);
        $this->repository->save();
    }

    /**
     * Deletes user for logged in user (account page).
     */
    public function deleteUserForUser(string $currentPassword, int $userId) : void
    {
        $user = $this->userHelper->getUserById($userId);

        if ($user->getPrivilegeLevel() === User::PRIVILEGE_LEVEL_OWNER) {
            throw InvalidOperationException::insufficientPrivileges($user->getPrivilegeLevelAsString());
        }

        if ( ! password_verify($currentPassword, $user->getPassword())) {
            throw InvalidArgumentException::incorrectPassword();
        }

        $this->userManagementService->deleteUser($user);

        $this->userSession->destroy();
    }
}
