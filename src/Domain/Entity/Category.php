<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use SamihSoylu\Journal\Domain\Entity\Trait\Identifiable;
use SamihSoylu\Journal\Domain\Entity\Trait\Timestampable;
use SamihSoylu\Journal\Domain\Repository\Doctrine\CategoryRepository;

#[Entity(repositoryClass: CategoryRepository::class), HasLifecycleCallbacks]
class Category extends BaseEntity
{
    use Identifiable, Timestampable;

    #[Column(length: 255)]
    protected string $name;

    #[Column(length: 255)]
    protected string $description;

    #[Column]
    protected int $position;

    #[PrePersist]
    public function checkErrors(): void
    {
        $requiredProperties = ['name', 'description', 'position'];

        $this->assertRequiredPropertiesProvided($requiredProperties);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Category
    {
        $this->description = $description;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): Category
    {
        $this->position = $position;

        return $this;
    }
}
