<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository\Interface;

interface SaveableInterface
{
    public function queueForSaving(object $entity): static;

    public function queueForRemoval(object $entity): static;

    public function saveChanges(): void;
}
