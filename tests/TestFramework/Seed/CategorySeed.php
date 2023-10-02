<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Seed;

use Ramsey\Uuid\UuidInterface;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;

final class CategorySeed
{
    private Category $category;

    public function __construct(
        private readonly TestOrmInterface $testOrm,
    ) {
        $this->category = new Category();

        $this->setDefaultValues();
    }

    private function setDefaultValues(): void
    {
        $this->category->setName('Food')
            ->setDescription('Keep track of your eating habits')
            ->setPosition(1);
    }

    public function withUser(User $user): self
    {
        $this->category->setUser($user);

        return $this;
    }

    public function withName(string $name): self
    {
        $this->category->setName($name);

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->category->setDescription($description);

        return $this;
    }

    public function withPosition(int $position): self
    {
        $this->category->setPosition($position);

        return $this;
    }

    public function save(): Category
    {
        $this->testOrm->persist($this->category);

        return $this->category;
    }
}