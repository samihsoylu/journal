<?php

declare(strict_types=1);

namespace Tests\TestFramework\Factory;

use App\Database\Model\Category;
use App\Database\Model\User;
use Tests\TestFramework\TestContext;

/**
 * Factory for creating Category entities in tests.
 *
 * Usage:
 *   $category = CategoryFactory::setup()
 *       ->withName('My Category')
 *       ->withUser($user)
 *       ->create();
 *
 * If no user is provided, one will be auto-created.
 */
final class CategoryFactory
{
    private ?User $user = null;
    private string $name = 'Test Category';
    private string $description = 'Test category description';
    private int $sortOrder = 0;

    private function __construct() {}

    public static function setup() : self
    {
        return new self();
    }

    public function withUser(User $user) : self
    {
        $clone = clone $this;
        $clone->user = $user;

        return $clone;
    }

    public function withName(string $name) : self
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    public function withDescription(string $description) : self
    {
        $clone = clone $this;
        $clone->description = $description;

        return $clone;
    }

    public function withSortOrder(int $sortOrder) : self
    {
        $clone = clone $this;
        $clone->sortOrder = $sortOrder;

        return $clone;
    }

    public function create() : Category
    {
        // Auto-create user if not provided
        $user = $this->user ?? UserFactory::setup()->create();

        $category = new Category();
        $category->setReferencedUser($user)
            ->setName($this->name)
            ->setDescription($this->description)
            ->setSortOrder($this->sortOrder)
        ;

        TestContext::$testOrm?->save($category);

        return $category;
    }
}
