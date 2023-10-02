<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository;

use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\Interface\SaveableInterface;

interface UserRepositoryInterface extends SaveableInterface
{
    public function getById(string $id): ?User;
}