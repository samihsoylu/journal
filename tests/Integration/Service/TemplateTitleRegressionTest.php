<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Database\Repository\TemplateRepository;
use App\Exception\UserException\InvalidParameterException;
use App\Exception\UserException\NotFoundException;
use App\Service\TemplateService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\CategoryFactory;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class TemplateTitleRegressionTest extends IntegrationTestCase
{
    #[Test]
    public function default_titles_use_the_selected_account_local_date_without_changing_encrypted_content() : void
    {
        $factory = UserFactory::setup()->withTimezone('Asia/Tokyo');
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $service = $this->getService(TemplateService::class);
        $service->createTemplate($user->getId(), $key, $category->getId(), 'Sleep', '<p>Private template</p>', '{template} {date} {time}');
        $template = $this->getRepository(TemplateRepository::class)->findByUser($user)[0];
        $before = $this->testOrm->fetchOneAssoc('SELECT content, defaultEntryTitlePattern FROM templates WHERE id = ?', [$template->getId()]);
        $json = $service->getTemplateForUser($template->getId(), $user->getId(), $key, '2024-02-29T23:30')->jsonSerialize();
        self::assertSame('Sleep 2024-02-29 23:30', $json['defaultEntryTitle']);
        self::assertSame($category->getId(), $json['categoryId']);
        self::assertSame('<p>Private template</p>', $json['content']);
        self::assertSame($before, $this->testOrm->fetchOneAssoc('SELECT content, defaultEntryTitlePattern FROM templates WHERE id = ?', [$template->getId()]));
        self::assertStringNotContainsString('Private template', $before['content']);
    }

    #[Test]
    public function older_update_calls_keep_the_pattern_and_an_explicit_empty_value_clears_it() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $service = $this->getService(TemplateService::class);
        $service->createTemplate($user->getId(), $key, $category->getId(), 'Sleep', '<p>Private</p>', 'Sleep {date}');
        $template = $this->getRepository(TemplateRepository::class)->findByUser($user)[0];
        $service->updateTemplate($user->getId(), $key, $category->getId(), $template->getId(), 'New sleep', '<p>New private</p>');
        self::assertSame('Sleep {date}', $service->getTemplateForUser($template->getId(), $user->getId(), $key)->getDefaultEntryTitlePattern());
        $service->updateTemplate($user->getId(), $key, $category->getId(), $template->getId(), 'New sleep', '<p>New private</p>', '');
        $json = $service->getTemplateForUser($template->getId(), $user->getId(), $key)->jsonSerialize();
        self::assertNull($json['defaultEntryTitlePattern']);
        self::assertNull($json['defaultEntryTitle']);
    }

    #[Test]
    public function invalid_patterns_do_not_change_existing_template_fields() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $service = $this->getService(TemplateService::class);
        $service->createTemplate($user->getId(), $key, $category->getId(), 'Sleep', '<p>Private</p>');
        $template = $this->getRepository(TemplateRepository::class)->findByUser($user)[0];
        $before = $this->testOrm->fetchOneAssoc('SELECT * FROM templates WHERE id = ?', [$template->getId()]);

        try {
            $service->updateTemplate($user->getId(), $key, $category->getId(), $template->getId(), 'Changed title', 'Changed text', '{unknown}');
            self::fail('Invalid pattern was accepted');
        } catch (InvalidParameterException) {
            self::assertSame('Sleep', $template->getTitle());
            self::assertSame($before, $this->testOrm->fetchOneAssoc('SELECT * FROM templates WHERE id = ?', [$template->getId()]));
        }
    }

    #[Test]
    public function another_account_cannot_read_the_pattern_or_generated_title() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $other = UserFactory::setup()->withUsername('other-user')->withEmailAddress('other@example.test')->create();
        $category = CategoryFactory::setup()->withUser($user)->create();
        $service = $this->getService(TemplateService::class);
        $service->createTemplate($user->getId(), $key, $category->getId(), 'Sleep', '<p>Private</p>', 'Sleep {date}');
        $template = $this->getRepository(TemplateRepository::class)->findByUser($user)[0];
        $this->expectException(NotFoundException::class);
        $service->getTemplateForUser($template->getId(), $other->getId(), $key, '2024-01-01T12:00');
    }
}
