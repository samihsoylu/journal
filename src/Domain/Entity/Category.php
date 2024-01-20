<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use LogicException;
use SamihSoylu\Journal\Domain\Entity\Trait\Identifiable;
use SamihSoylu\Journal\Domain\Entity\Trait\Timestampable;
use SamihSoylu\Journal\Domain\Repository\Doctrine\CategoryRepository;
use SamihSoylu\Utility\Assert;

#[Entity(repositoryClass: CategoryRepository::class), HasLifecycleCallbacks]
class Category extends BaseEntity
{
    use Identifiable, Timestampable;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'userId', nullable: false)]
    protected User $user;

    #[Column(length: 255)]
    protected string $name;

    #[Column(length: 255)]
    protected string $description;

    #[Column]
    protected int $position;

    #[PrePersist]
    public function checkErrors(): void
    {
        $requiredProperties = ['user', 'name', 'description', 'position'];

        $this->assertRequiredPropertiesProvided($requiredProperties);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function assertBelongsToUser(User $user): void
    {
        $providedUserId = $user?->getId()?->toString();
        Assert::notNull($providedUserId, 'The user passed in the method parameter does not have an id');

        $ownerUserId = $this->user?->getId()?->toString();
        Assert::notNull($ownerUserId, 'This category belongs to a user that does not have an id');

        if ($providedUserId !== $ownerUserId) {
            throw new LogicException(
                "Category[id={$ownerUserId}] does not belong to User[id={$providedUserId}]"
            );
        }
    }
}
