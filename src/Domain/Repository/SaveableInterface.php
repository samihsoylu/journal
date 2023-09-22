<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository;

use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Domain\Repository\Doctrine\Trait\Saveable;

/**
 * Implemented in trait @see Saveable
 */
interface SaveableInterface
{
    public function queueForSaving(object $entity): static;

    public function queueForRemoval(object $entity): static;

    public function saveChanges(): void;
}