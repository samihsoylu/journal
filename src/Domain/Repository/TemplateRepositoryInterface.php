<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository;

use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Repository\Interface\SaveableInterface;

interface TemplateRepositoryInterface extends SaveableInterface
{
    public function getById(string $id): ?Template;
}
