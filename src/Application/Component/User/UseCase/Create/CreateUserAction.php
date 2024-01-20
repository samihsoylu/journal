<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Application\Component\User\UseCase\Create;

use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Infrastructure\Port\Action\ActionInterface;

/**
 * @see CreateUserActionHandler
 */
final readonly class CreateUserAction implements ActionInterface
{
    public function __construct(
        public string $username,
        public string $passwordTransientCacheId,
        public string $emailAddress,
        public Role $role,
    ) {}
}
