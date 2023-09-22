<?php

namespace SamihSoylu\Journal\Domain\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use SamihSoylu\Journal\Domain\Entity\Entry;
use SamihSoylu\Journal\Domain\Repository\Doctrine\Trait\Saveable;
use SamihSoylu\Journal\Domain\Repository\EntryRepositoryInterface;

final class EntryRepository extends EntityRepository implements EntryRepositoryInterface
{
    use Saveable;

    public function getAll(): array
    {
        // TODO: Implement getAll() method.
    }

    public function getById(string $id): ?Entry
    {
        // TODO: Implement getById() method.
    }
}
