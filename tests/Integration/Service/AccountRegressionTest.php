<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Database\Model\User;
use App\Database\Repository\CategoryRepository;
use App\Database\Repository\TemplateRepository;
use App\Database\Repository\UserRepository;
use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\InvalidOperationException;
use App\Service\Security\UserKeys;
use App\Service\UserManagementService;
use App\Service\UserService;
use App\Service\WidgetService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class AccountRegressionTest extends IntegrationTestCase
{
    #[Test]
    public function account_creation_preserves_default_categories_and_templates() : void
    {
        $id = $this->getService(UserManagementService::class)->createUser('newuser', 'testpassword', 'new@example.test', User::PRIVILEGE_LEVEL_USER);
        $user = $this->testOrm->fetchOneAssoc('SELECT * FROM users WHERE id = ?', [$id]);
        self::assertTrue(password_verify('testpassword', $user['password']));

        foreach (['categories', 'templates'] as $table) {
            self::assertNotEmpty($this->testOrm->fetchAllAssoc('SELECT * FROM ' . $table . ' WHERE userId = ?', [$id]));
        }
    }

    #[Test]
    public function shared_default_setup_keeps_two_users_and_their_keys_separate() : void
    {
        $management = $this->getService(UserManagementService::class);
        $ids = [
            $management->createUser('first-user', 'first-password', 'first@example.test', User::PRIVILEGE_LEVEL_USER),
            $management->createUser('second-user', 'second-password', 'second@example.test', User::PRIVILEGE_LEVEL_USER),
        ];

        foreach ($ids as $index => $id) {
            $user = $this->getRepository(UserRepository::class)->getById($id);
            $password = $index === 0 ? 'first-password' : 'second-password';
            $key = $this->getService(UserKeys::class)->getKeyFromProtectedKey($user->getEncryptionKey(), $password);
            $categories = $this->getRepository(CategoryRepository::class)->findByUser($user);
            self::assertSame(['Personal', 'Food', 'Work'], array_map(static fn ($category) => $category->getName(), $categories));
            $templates = $this->getRepository(TemplateRepository::class)->findByUser($user);
            self::assertCount(1, $templates);
            self::assertSame($id, $templates[0]->getReferencedCategory()->getReferencedUser()->getId());
            self::assertStringContainsString('<h1>Breakfast</h1>', $templates[0]->getContentDecrypted($key));
        }
    }

    #[Test]
    public function quick_add_can_be_enabled_and_disabled_without_duplicate_widgets() : void
    {
        $user = UserFactory::setup()->create();
        $widgets = $this->getService(WidgetService::class);
        $widgets->updateWidgetSettingsForUser($user->getId(), ['quick-add-entries-box' => true]);
        self::assertSame(['quick-add-entries-box' => true], $widgets->getEnabledWidgetsForUser($user->getId()));
        $widgets->updateWidgetSettingsForUser($user->getId(), []);
        self::assertSame([], $widgets->getEnabledWidgetsForUser($user->getId()));
        self::assertCount(1, $this->testOrm->fetchAllAssoc('SELECT * FROM widgets WHERE userId = ?', [$user->getId()]));
    }

    #[Test]
    public function incorrect_current_password_leaves_credentials_unchanged() : void
    {
        $user = UserFactory::setup()->create();
        $before = $this->testOrm->fetchOneAssoc('SELECT password, encryptionKey FROM users WHERE id = ?', [$user->getId()]);

        try {
            $this->getService(UserService::class)->changePassword($user->getId(), 'wrong', 'new-password');
            self::fail('Password change accepted incorrect credentials');
        } catch (InvalidArgumentException) {
            self::assertSame($before, $this->testOrm->fetchOneAssoc('SELECT password, encryptionKey FROM users WHERE id = ?', [$user->getId()]));
        }
    }

    #[Test]
    public function an_admin_cannot_create_an_owner() : void
    {
        $admin = UserFactory::setup()->withPrivilegeLevel(User::PRIVILEGE_LEVEL_ADMIN)->create();
        $this->expectException(InvalidOperationException::class);
        $this->getService(UserManagementService::class)->createUserForAdmin($admin->getId(), 'owner', 'password', 'owner@example.test', User::PRIVILEGE_LEVEL_OWNER);
    }

    #[Test]
    public function an_admin_cannot_delete_the_owner() : void
    {
        $admin = UserFactory::setup()->withPrivilegeLevel(User::PRIVILEGE_LEVEL_ADMIN)->create();
        $owner = UserFactory::setup()->withUsername('owner')->withEmailAddress('owner@example.test')->withPrivilegeLevel(User::PRIVILEGE_LEVEL_OWNER)->create();
        $this->expectException(InvalidOperationException::class);
        $this->getService(UserManagementService::class)->deleteUserForAdmin($admin->getId(), $owner->getId());
    }

    #[Test]
    public function a_weak_new_password_cannot_change_existing_credentials() : void
    {
        $user = UserFactory::setup()->withPassword('old')->create();
        $before = $this->testOrm->fetchOneAssoc('SELECT password, encryptionKey FROM users WHERE id = ?', [$user->getId()]);

        try {
            $this->getService(UserService::class)->changePassword($user->getId(), 'old', '');
            self::fail('An empty new password was accepted');
        } catch (InvalidArgumentException) {
            self::assertSame($before, $this->testOrm->fetchOneAssoc('SELECT password, encryptionKey FROM users WHERE id = ?', [$user->getId()]));
        }
    }

    #[Test]
    public function a_weak_password_cannot_create_a_new_account() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getService(UserManagementService::class)->createUser('new-user', '', 'new@example.test', User::PRIVILEGE_LEVEL_USER);
    }
}
