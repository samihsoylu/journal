<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\TestDouble\Fake;

use Ramsey\Uuid\UuidInterface;
use SamihSoylu\Journal\Domain\Entity\Trait\Identifiable;

final class FakeEntity
{
    use Identifiable;

    public function setId(UuidInterface $id): self
    {
        $this->id = $id;

        return $this;
    }
}