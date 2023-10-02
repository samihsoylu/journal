<?php

declare(strict_types=1);

namespace SamihSoylu\Journal\Tests\TestFramework\Seed;

use Ramsey\Uuid\UuidInterface;
use SamihSoylu\CipherSuite\PasswordKeyManager\PasswordKeyManagerInterface;
use SamihSoylu\Journal\Domain\Entity\Category;
use SamihSoylu\Journal\Domain\Entity\Enum\Role;
use SamihSoylu\Journal\Domain\Entity\Template;
use SamihSoylu\Journal\Domain\Entity\User;
use SamihSoylu\Journal\Infrastructure\Port\PasswordHasher\PasswordHasherInterface;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmInterface;

final class TemplateSeed
{
    private Template $template;

    public function __construct(
        private readonly TestOrmInterface $testOrm,
    ) {
        $this->template = new Template();

        $this->setDefaultValues();
    }

    private function setDefaultValues(): void
    {
        $this->template->setTitle('Food Tracking')
            ->setContent("Breakfast:\n");
    }

    public function withTitle(string $title): self
    {
        $this->template->setTitle($title);

        return $this;
    }
    public function withContent(string $content): self
    {
        $this->template->setContent($content);

        return $this;
    }
    public function withCategory(Category $category): self
    {
        $this->template->setCategory($category);

        return $this;
    }
    public function withUser(User $user): self
    {
        $this->template->setUser($user);

        return $this;
    }

    public function save(): Template
    {
        $this->testOrm->persist($this->template);

        return $this->template;
    }
}