<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository\Doctrine;

use Doctrine\ORM\EntityRepository;
use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Repository\Doctrine\Trait\Saveable;
use SamihSoylu\Journal\Domain\Repository\TemplateRepositoryInterface;

final class TemplateRepository extends EntityRepository implements TemplateRepositoryInterface
{
    use Saveable;

    public function getById(string $id): ?Template
    {
        return $this->find($id);
    }
}
