<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Component\User\UseCase\Create;

use Psr\EventDispatcher\EventDispatcherInterface;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Application\Component\User\Event\UserCreatedEvent;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Action\ActionHandlerInterface;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Action\ActionInterface;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Cache\SecureCacheable;
use SamihSoylu\Journal\Framework\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;
use SamihSoylu\Utility\Assert;

/**
 * @implements ActionHandlerInterface<CreateUserAction>
 */
final readonly class CreateUserActionHandler implements ActionHandlerInterface
{
    public function __construct(
        private SecureCacheable $secureCache,
        private UserRepositoryInterface $userRepository,
        private PasswordKeyManagerInterface $passwordKeyManager,
        private EventDispatcherInterface $eventDispatcher,
        private PasswordHasherInterface $passwordHasher,
    ) {}

    public function __invoke(ActionInterface $action): void
    {
        $password = $this->getPasswordFromEncryptedCache($action->passwordTransientCacheId);

        $protectedKey = $this->passwordKeyManager->createProtectedKeyForDb($password);
        $hashedPassword = $this->passwordHasher->hash($password);

        $user = new User();
        $user->setUsername($action->username)
            ->setPassword($hashedPassword)
            ->setEmailAddress($action->emailAddress)
            ->setRole($action->role)
            ->setProtectedKey($protectedKey);

        $this->userRepository
            ->queueForSaving($user)
            ->saveChanges();

        $this->eventDispatcher->dispatch(
            new UserCreatedEvent(
                $user->getId()->toString(),
                $action->passwordTransientCacheId,
            )
        );
    }

    private function getPasswordFromEncryptedCache(string $transientId): string
    {
        $password = $this->secureCache->get($transientId);
        Assert::notNull($password, "CacheItem[transientId={$transientId}] not found");

        return $password;
    }
}
