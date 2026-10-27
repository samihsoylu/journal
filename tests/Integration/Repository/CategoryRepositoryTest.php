<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Database\Repository\CategoryRepository;
use Override;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\CategoryFactory;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class CategoryRepositoryTest extends IntegrationTestCase
{
    private CategoryRepository $repository;

    #[Override]
    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = $this->getRepository(CategoryRepository::class);
    }

    #[Test]
    public function it_should_save_category() : void
    {
        $user = UserFactory::setup()->create();
        $category = CategoryFactory::setup()
            ->withUser($user)
            ->withName('Test Category')
            ->withDescription('Test description')
            ->create()
        ;

        $row = $this->testOrm->fetchOneAssoc(
            'SELECT * FROM categories WHERE id = ?',
            [$category->getId()],
        );

        self::assertNotNull($row, 'Category not found in database');
        self::assertSame('Test Category', $row['name']);
        self::assertSame('Test description', $row['description']);
    }

    #[Test]
    public function it_should_find_categories_by_user() : void
    {
        $user = UserFactory::setup()->create();
        CategoryFactory::setup()->withUser($user)->withName('Category 1')->create();
        CategoryFactory::setup()->withUser($user)->withName('Category 2')->create();

        $result = $this->repository->findByUser($user);

        self::assertCount(2, $result);
    }

    #[Test]
    public function it_should_return_categories_sorted_by_sort_order() : void
    {
        $user = UserFactory::setup()->create();
        CategoryFactory::setup()->withUser($user)->withName('Third')->withSortOrder(3)->create();
        CategoryFactory::setup()->withUser($user)->withName('First')->withSortOrder(1)->create();
        CategoryFactory::setup()->withUser($user)->withName('Second')->withSortOrder(2)->create();

        $result = $this->repository->findByUser($user);

        self::assertCount(3, $result);
        self::assertSame('First', $result[0]->getName());
        self::assertSame('Second', $result[1]->getName());
        self::assertSame('Third', $result[2]->getName());
    }

    #[Test]
    public function it_should_find_category_by_name() : void
    {
        $user = UserFactory::setup()->create();
        CategoryFactory::setup()
            ->withUser($user)
            ->withName('Unique Category')
            ->create()
        ;

        $result = $this->repository->findByCategoryName($user, 'Unique Category');

        self::assertNotNull($result);
        self::assertSame('Unique Category', $result->getName());
    }

    #[Test]
    public function it_should_return_null_when_category_name_not_found() : void
    {
        $user = UserFactory::setup()->create();

        $result = $this->repository->findByCategoryName($user, 'Nonexistent');

        self::assertNull($result);
    }

    #[Test]
    public function it_should_only_find_categories_for_specified_user() : void
    {
        $user1 = UserFactory::setup()->withUsername('user1')->withEmailAddress('user1@test.com')->create();
        $user2 = UserFactory::setup()->withUsername('user2')->withEmailAddress('user2@test.com')->create();

        CategoryFactory::setup()->withUser($user1)->withName('User1 Category')->create();
        CategoryFactory::setup()->withUser($user2)->withName('User2 Category')->create();

        $result = $this->repository->findByUser($user1);

        self::assertCount(1, $result);
        self::assertSame('User1 Category', $result[0]->getName());
    }

    #[Test]
    public function it_should_get_category_by_id() : void
    {
        $category = CategoryFactory::setup()
            ->withName('Get By ID Test')
            ->create()
        ;

        $result = $this->repository->getById($category->getId());

        self::assertNotNull($result);
        self::assertSame('Get By ID Test', $result->getName());
    }
}
