<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Service;

use SamihSoylu\Journal\Application\Component\User\UseCase\Create\CreateUserAction;
use SamihSoylu\Journal\Application\Service\Contract\UserServiceInterface;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Action\ActionDispatcherInterface;
use SamihSoylu\Journal\Framework\Infrastructure\Port\Cache\SecureCacheable;
use SamihSoylu\Utility\StringHelper;

final readonly class UserService implements UserServiceInterface
{
    public function __construct(
        private ActionDispatcherInterface $actionDispatcher,
        private SecureCacheable $secureCache,
    ) {}

    public function createUser(
        string $username,
        #[\SensitiveParameter]
        string $password,
        string $emailAddress,
        Role $role,
    ): void {
        $passwordTransientCacheId = StringHelper::createRandomString();
        $this->secureCache->set($passwordTransientCacheId, $password);

        $this->actionDispatcher->dispatch(
            new CreateUserAction(
                $username,
                $passwordTransientCacheId,
                $emailAddress,
                $role,
            )
        );
    }
}
