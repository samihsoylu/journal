<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Seed;

use Ramsey\Uuid\UuidInterface;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\Entry;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;

final class EntrySeed
{
    private Entry $entry;

    public function __construct(
        private readonly TestOrmInterface $testOrm,
    ) {
        $this->entry = new Entry();

        $this->setDefaultValues();
    }

    private function setDefaultValues(): void
    {
        $this->entry->setTitle('Food record 01-01-2024')
            ->setContent('Food tracking category');
    }

    public function withUser(User $user): self
    {
        $this->entry->setUser($user);

        return $this;
    }

    public function withCategory(Category $category): self
    {
        $this->entry->setCategory($category);

        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->entry->setTitle($title);

        return $this;
    }

    public function withContent(string $content): self
    {
        $this->entry->setContent($content);

        return $this;
    }

    public function save(): Entry
    {
        $this->testOrm->persist($this->entry);

        return $this->entry;
    }
}