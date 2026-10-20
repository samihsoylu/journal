<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Database\Repository\EntryRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\CategoryFactory;
use Tests\TestFramework\Factory\EntryFactory;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class EntryRepositoryTest extends IntegrationTestCase
{
    #[Test]
    public function category_search_preserves_ownership_counts_and_pagination() : void
    {
        $user = UserFactory::setup()->create();
        $other = UserFactory::setup()->withUsername('other')->withEmailAddress('other@example.test')->create();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $foreignCategory = CategoryFactory::setup()->withUser($other)->create();
        $entry = EntryFactory::setup()->withUser($user)->withCategory($category)->withTitle('Needle in own category')->create();
        EntryFactory::setup()->withUser($user)->withCategory($category)->withTitle('Unrelated entry')->create();
        EntryFactory::setup()->withUser($other)->withCategory($foreignCategory)->withTitle('Needle in private category')->create();
        $repository = $this->getRepository(EntryRepository::class);
        $entries = $repository->getEntriesBySearchQueryLimitCategoryStartEndDateAndOffset($user->getId(), 'Needle', $category->getId(), 0, time() + 60, 0, 1);
        self::assertCount(1, $entries);
        self::assertSame($entry->getId(), $entries[0]->getId());
        self::assertSame(1, $repository->getTotalCountOfEntriesBySearchQueryLimitCategoryStartEndDateAndOffset($user->getId(), 'Needle', $category->getId(), 0, time() + 60));
        self::assertSame([], $repository->getEntriesBySearchQueryLimitCategoryStartEndDateAndOffset($user->getId(), 'Needle', $category->getId(), null, null, 1, 1));
        self::assertSame([], $repository->getEntriesBySearchQueryLimitCategoryStartEndDateAndOffset($user->getId(), null, $foreignCategory->getId(), null, null, 0, 25));
        self::assertSame(0, $repository->getTotalCountOfEntriesBySearchQueryLimitCategoryStartEndDateAndOffset($user->getId(), null, $foreignCategory->getId(), null, null));
    }
}
