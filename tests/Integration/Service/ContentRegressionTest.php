<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Database\Model\Entry;
use App\Database\Model\Template;
use App\Exception\UserException\NotFoundException;
use App\Service\CategoryService;
use App\Service\EntryService;
use App\Service\TemplateService;
use App\Service\UserService;
use App\Utility\Encryptor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\CategoryFactory;
use Tests\TestFramework\Factory\EntryFactory;
use Tests\TestFramework\Factory\TemplateFactory;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ContentRegressionTest extends IntegrationTestCase
{
    #[Test]
    public function existing_ciphertext_survives_password_change_and_orm_reload() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $entry = EntryFactory::setup()->withUser($user)->withCategory($category)
            ->withContent('<p>Private entry – café</p>', $key)->create()
        ;
        $template = TemplateFactory::setup()->withUser($user)->withCategory($category)
            ->withContent('<p>Private template</p>', $key)->create()
        ;
        $entryId = $entry->getId();
        $templateId = $template->getId();
        $userId = $user->getId();
        $entryCiphertext = $entry->getContent();
        $templateCiphertext = $template->getContent();

        $this->getService(UserService::class)->changePassword($userId, 'testpassword', 'changed-password');
        $row = $this->testOrm->fetchOneAssoc('SELECT * FROM users WHERE id = ?', [$userId]);
        self::assertTrue(password_verify('changed-password', $row['password']));
        self::assertFalse(password_verify('testpassword', $row['password']));
        $newKey = new Encryptor()->getKeyFromProtectedKey($row['encryptionKey'], 'changed-password');
        self::assertSame($key->saveToAsciiSafeString(), $newKey->saveToAsciiSafeString());
        $em = $this->getService(EntityManagerInterface::class);
        $em->clear();
        $reloadedEntry = $em->find(Entry::class, $entryId);
        $reloadedTemplate = $em->find(Template::class, $templateId);
        self::assertSame($entryCiphertext, $reloadedEntry->getContent());
        self::assertSame($templateCiphertext, $reloadedTemplate->getContent());
        self::assertSame('<p>Private entry – café</p>', $reloadedEntry->getContentDecrypted($newKey));
        self::assertSame('<p>Private template</p>', $reloadedTemplate->getContentDecrypted($newKey));
    }

    #[Test]
    public function entries_can_be_created_updated_read_and_deleted() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $service = $this->getService(EntryService::class);
        $id = $service->createEntry($user->getId(), $key, $category->getId(), 'Original title', '<p>Original</p>');
        $stored = $this->testOrm->fetchOneAssoc('SELECT * FROM entries WHERE id = ?', [$id]);
        self::assertNotSame('<p>Original</p>', $stored['content']);
        self::assertGreaterThan(0, $stored['createdTimestamp']);
        self::assertGreaterThan(0, $stored['lastUpdatedTimestamp']);
        $service->updateEntry($user->getId(), $key, $id, $category->getId(), 'Updated title', '<p>Updated</p>');
        self::assertSame('<p>Updated</p>', $service->getEntryForUser($id, $user->getId(), $key)->getContent());
        self::assertSame('Updated title', $service->getEntryForUser($id, $user->getId(), $key)->getTitle());
        $service->deleteEntry($id, $user->getId());
        self::assertNull($this->testOrm->fetchOneAssoc('SELECT * FROM entries WHERE id = ?', [$id]));
    }

    #[Test]
    public function templates_can_be_created_updated_read_and_deleted() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $service = $this->getService(TemplateService::class);
        $service->createTemplate($user->getId(), $key, $category->getId(), 'Daily template', '<p>Original</p>');
        $templates = $service->getAllTemplatesForUser($user->getId());
        self::assertCount(1, $templates);
        $id = $templates[0]->getId();
        $service->updateTemplate($user->getId(), $key, $category->getId(), $id, 'Updated template', '<p>Updated</p>');
        self::assertSame('<p>Updated</p>', $service->getTemplateForUser($id, $user->getId(), $key)->getContent());
        $service->deleteTemplate($id, $user->getId());
        self::assertSame([], $service->getAllTemplatesForUser($user->getId()));
    }

    #[Test]
    public function deleting_a_category_preserves_its_entries_and_templates() : void
    {
        $user = UserFactory::setup()->create();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $entry = EntryFactory::setup()->withUser($user)->withCategory($category)->create();
        $template = TemplateFactory::setup()->withUser($user)->withCategory($category)->create();
        $categoryId = $category->getId();
        $this->getService(CategoryService::class)->deleteCategory($user->getId(), $categoryId);
        $storedEntry = $this->testOrm->fetchOneAssoc('SELECT * FROM entries WHERE id = ?', [$entry->getId()]);
        $storedTemplate = $this->testOrm->fetchOneAssoc('SELECT * FROM templates WHERE id = ?', [$template->getId()]);
        self::assertNotSame($categoryId, $storedEntry['categoryId']);
        self::assertSame($storedEntry['categoryId'], $storedTemplate['categoryId']);
        self::assertSame($entry->getContent(), $storedEntry['content']);
        self::assertSame($template->getContent(), $storedTemplate['content']);
    }

    #[Test]
    public function another_user_cannot_delete_an_entry() : void
    {
        $owner = UserFactory::setup()->create();
        $intruder = UserFactory::setup()->withUsername('intruder')->withEmailAddress('intruder@example.test')->create();
        $entry = EntryFactory::setup()->withUser($owner)->create();
        $this->expectException(NotFoundException::class);
        $this->getService(EntryService::class)->deleteEntry($entry->getId(), $intruder->getId());
    }

    #[Test]
    public function another_users_category_cannot_receive_a_new_entry() : void
    {
        $owner = UserFactory::setup()->create();
        $factory = UserFactory::setup()->withUsername('intruder')->withEmailAddress('intruder@example.test');
        $intruder = $factory->create();
        $category = CategoryFactory::setup()->withUser($owner)->create();
        $this->expectException(NotFoundException::class);
        $this->getService(EntryService::class)->createEntry($intruder->getId(), $factory->getDecryptionKey(), $category->getId(), 'Foreign category', 'Denied');
    }

    #[Test]
    public function another_user_cannot_delete_a_template() : void
    {
        $owner = UserFactory::setup()->create();
        $intruder = UserFactory::setup()->withUsername('intruder')->withEmailAddress('intruder@example.test')->create();
        $template = TemplateFactory::setup()->withUser($owner)->create();
        $this->expectException(NotFoundException::class);
        $this->getService(TemplateService::class)->deleteTemplate($template->getId(), $intruder->getId());
    }
}
