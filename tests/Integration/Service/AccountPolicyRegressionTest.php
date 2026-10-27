<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Database\Model\User;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Exception\UserException\NotFoundException;
use App\Service\CategoryService;
use App\Service\EntryService;
use App\Service\TemplateService;
use App\Service\UserManagementService;
use App\Service\UserService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\CategoryFactory;
use Tests\TestFramework\Factory\TemplateFactory;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/** @internal
 * @coversNothing
 */
final class AccountPolicyRegressionTest extends IntegrationTestCase
{
    #[Test]
    #[DataProvider('provideAccount_creation_rejects_duplicate_identity_without_adding_rowsCases')]
    public function account_creation_rejects_duplicate_identity_without_adding_rows(string $username, string $email) : void
    {
        UserFactory::setup()->create();
        $before = $this->testOrm->fetchAllAssoc('SELECT id, username, emailAddress FROM users');

        try {
            $this->getService(UserManagementService::class)->createUser($username, 'new-password', $email, User::PRIVILEGE_LEVEL_USER);
            self::fail('Duplicate identity was accepted');
        } catch (InvalidArgumentException) {
            self::assertSame($before, $this->testOrm->fetchAllAssoc('SELECT id, username, emailAddress FROM users'));
        }
    }

    public static function provideAccount_creation_rejects_duplicate_identity_without_adding_rowsCases() : iterable
    {
        return [['testuser', 'different@example.test'], ['different', 'test@example.com']];
    }

    #[Test]
    public function administrators_cannot_grant_their_own_privilege_level() : void
    {
        $admin = UserFactory::setup()->withPrivilegeLevel(User::PRIVILEGE_LEVEL_ADMIN)->create();
        $target = UserFactory::setup()->withUsername('target')->withEmailAddress('target@example.test')->create();
        $this->expectException(InvalidOperationException::class);
        $this->getService(UserManagementService::class)->updateUserPrivilegesForAdmin($admin->getId(), $target->getId(), User::PRIVILEGE_LEVEL_ADMIN);
    }

    #[Test]
    public function deleting_a_missing_user_is_a_not_found_error() : void
    {
        $this->expectException(NotFoundException::class);
        $this->getService(UserService::class)->getUser(2147483647);
    }

    #[Test]
    public function duplicate_categories_are_rejected_per_account() : void
    {
        $user = UserFactory::setup()->create();
        CategoryFactory::setup()->withUser($user)->withName('Duplicate')->create();
        $this->expectException(InvalidArgumentException::class);
        $this->getService(CategoryService::class)->createCategory($user->getId(), 'Duplicate', 'description');
    }

    #[Test]
    public function duplicate_template_titles_are_rejected_per_account() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $category = CategoryFactory::setup()->withUser($user)->create();
        TemplateFactory::setup()->withUser($user)->withCategory($category)->withTitle('Duplicate')->withContent('content', $factory->getDecryptionKey())->create();
        $this->expectException(InvalidArgumentException::class);
        $this->getService(TemplateService::class)->createTemplate($user->getId(), $factory->getDecryptionKey(), $category->getId(), 'Duplicate', 'content');
    }

    #[Test]
    public function unknown_entry_category_cannot_create_content() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $this->expectException(NotFoundException::class);
        $this->getService(EntryService::class)->createEntry($user->getId(), $factory->getDecryptionKey(), 2147483647, 'Entry', 'content');
    }
}
