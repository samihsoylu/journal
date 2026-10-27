<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\NotFoundException;
use App\Service\CategoryService;
use App\Service\EntryExporter;
use App\Service\EntryService;
use App\Service\TemplateService;
use App\Service\UserService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\CategoryFactory;
use Tests\TestFramework\Factory\EntryFactory;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/** @internal
 * @coversNothing
 */
final class ContentBoundaryTest extends IntegrationTestCase
{
    #[Test]
    public function an_account_without_entries_does_not_publish_an_empty_export() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        self::assertNull($this->getService(EntryExporter::class)->export($user->getId(), $user->getUsername(), $factory->getDecryptionKey()));
        self::assertDirectoryDoesNotExist(EXPORT_CACHE_PATH . '/' . $user->getId());
    }

    #[Test]
    public function invalid_timezone_cannot_change_a_persisted_preference() : void
    {
        $user = UserFactory::setup()->withTimezone('Europe/Amsterdam')->create();

        try {
            $this->getService(UserService::class)->setDateTimeZoneForUser($user->getId(), 'Invalid/Timezone');
            self::fail('Invalid timezone was persisted');
        } catch (InvalidArgumentException) {
            self::assertSame('Europe/Amsterdam', $this->testOrm->fetchOneAssoc('SELECT timezone FROM users WHERE id = ?', [$user->getId()])['timezone']);
        }
    }

    #[Test]
    #[DataProvider('provideMissing_content_is_reported_without_a_null_dereferenceCases')]
    public function missing_content_is_reported_without_a_null_dereference(string $kind) : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $this->expectException(NotFoundException::class);
        match ($kind) {
            'entry' => $this->getService(EntryService::class)->getEntryForUser(2147483647, $user->getId(), $factory->getDecryptionKey()),
            'template' => $this->getService(TemplateService::class)->getTemplateForUser(2147483647, $user->getId(), $factory->getDecryptionKey()),
            'category' => $this->getService(CategoryService::class)->getCategoryForUser(2147483647, $user->getId()),
            default => self::fail('Unknown content fixture'),
        };
    }

    public static function provideMissing_content_is_reported_without_a_null_dereferenceCases() : iterable
    {
        return [['entry'], ['template'], ['category']];
    }

    #[Test]
    public function page_zero_returns_the_first_page_instead_of_a_negative_offset() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $entry = EntryFactory::setup()->withUser($user)->withCategory($category)->withContent('Entry body', $factory->getDecryptionKey())->create();
        $page = $this->getService(EntryService::class)->getAllEntriesForUserFromFilter($user->getId(), null, null, null, null, 0);
        self::assertSame(1, $page->getCurrentPage());
        self::assertSame(1, $page->getTotalPages());
        self::assertSame([$entry->getId()], array_map(static fn ($item) => $item->getId(), $page->getEntries()));
    }
}
