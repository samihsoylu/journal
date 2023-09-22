<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Domain\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\PrePersist;
use SamihSoylu\Journal\Domain\Entity\Trait\Identifiable;
use SamihSoylu\Journal\Domain\Entity\Trait\Timestampable;
use SamihSoylu\Journal\Domain\Repository\Doctrine\EntryRepository;

#[Entity(repositoryClass: EntryRepository::class), HasLifecycleCallbacks]
class Entry extends BaseEntity
{
    use Identifiable, Timestampable;

    #[Column(length: 255)]
    protected string $title;

    #[Column(type: Types::TEXT)]
    protected string $content;

    #[ManyToOne]
    #[JoinColumn(nullable: false)]
    protected Category $category;

    #[ManyToOne]
    #[JoinColumn(nullable: false)]
    protected User $user;

    #[PrePersist]
    public function checkErrors(): void
    {
        $requiredProperties = ['title', 'content', 'category', 'user'];

        $this->assertRequiredPropertiesProvided($requiredProperties);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
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
}
