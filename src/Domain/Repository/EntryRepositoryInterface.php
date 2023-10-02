<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository;

use SamihSoylu\Journal\Domain\Entity\Entry;
use SamihSoylu\Journal\Domain\Repository\Interface\SaveableInterface;

interface EntryRepositoryInterface extends SaveableInterface
{
    public function getById(string $id): ?Entry;
}