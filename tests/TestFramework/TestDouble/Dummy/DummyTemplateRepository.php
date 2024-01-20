<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Dummy;

use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Repository\TemplateRepositoryInterface;

final class DummyTemplateRepository implements TemplateRepositoryInterface
{
    public function queueForSaving(object $entity): static
    {
        return $this;
    }

    public function queueForRemoval(object $entity): static
    {
        return $this;
    }

    public function saveChanges(): void
    {
    }

    public function getById(string $id): ?Template
    {
        return null;
    }
}
