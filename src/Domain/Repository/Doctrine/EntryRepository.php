<?php

namespace SamihSoylu\Journal\Domain\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use SamihSoylu\Journal\Domain\Entity\Entry;
use SamihSoylu\Journal\Domain\Repository\Doctrine\Trait\Saveable;
use SamihSoylu\Journal\Domain\Repository\EntryRepositoryInterface;

final class EntryRepository extends EntityRepository implements EntryRepositoryInterface
{
    use Saveable;

    public function getById(string $id): ?Entry
    {
        return $this->find($id);
    }
}
