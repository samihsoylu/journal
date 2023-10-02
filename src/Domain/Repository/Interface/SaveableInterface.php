<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository\Interface;

use SamihSoylu\Journal\Domain\Repository\Doctrine\Trait\Saveable;

interface SaveableInterface
{
    public function queueForSaving(object $entity): static;

    public function queueForRemoval(object $entity): static;

    public function saveChanges(): void;
}