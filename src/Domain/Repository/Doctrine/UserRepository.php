<?php

namespace SamihSoylu\Journal\Domain\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\Lazy\LazyUuidFromString;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\Doctrine\Trait\Saveable;
use SamihSoylu\Journal\Domain\Repository\UserRepositoryInterface;

final class UserRepository extends EntityRepository implements UserRepositoryInterface
{
    use Saveable;

    public function getById(string $id): ?User
    {
        return $this->find($id);
    }
}
