<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Repository\Doctrine\Trait;

/**
 * Classes using this trait must extend Doctrine\ORM\EntityRepository
 */
trait Saveable
{
    public function queueForSaving(object $entity): static
    {
        $this->getEntityManager()->persist($entity);

        return $this;
    }

    public function queueForRemoval(object $entity): static
    {
        $this->getEntityManager()->remove($entity);

        return $this;
    }

    public function saveChanges(): void
    {
        $this->getEntityManager()->flush();
    }
}