<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Core\User\UseCase\Create;

use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;
use SamihSoylu\Journal\Infrastructure\Port\Cache\Cacheable;
use SamihSoylu\Utility\Assert;

final readonly class CreateUserActionHandler implements ActionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private Cacheable $encryptedTransientCache,
        private PasswordKeyManagerInterface $passwordKeyManager,
    ) {}

    /**
     * @param CreateUserAction $action
     * @return User
     */
    public function __invoke(ActionInterface $action): void
    {
        $password = $this->getPasswordFromEncryptedCache($action->passwordTransientCacheId);

        $protectedKey = $this->passwordKeyManager->createProtectedKeyForDb($password);
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);

        $user = new User();
        $user->setUsername($action->username)
            ->setPassword($hashedPassword)
            ->setRole($action->role)
            ->setEncryptionKey($protectedKey);

        $this->repository
            ->queueForSaving($user)
            ->saveChanges();
    }

    private function getPasswordFromEncryptedCache(string $transientId): string
    {
        $password = $this->encryptedTransientCache->get($transientId);
        Assert::notNull($password, "CacheItem[transientId={$transientId}] not found");

        return $password;
    }
}