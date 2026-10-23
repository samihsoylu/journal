<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Database\Repository\EntryRepository;
use App\Exception\UserException\ActionNotPermittedException;
use App\Exception\UserException\InvalidOperationException;
use App\Service\EntryExporter;
use App\Service\Export\ExportArchiveStore;
use App\Service\Export\ExportJobRunner;
use App\Service\Helper\MediaHelper;
use App\Service\Helper\UserHelper;
use App\Service\MediaService;
use App\Service\UserExportService;
use App\Service\UserManagementService;
use App\Service\ValueObject\Image;
use App\Utility\BladeRenderer;
use App\Utility\Encryptor;
use App\Utility\Export\ExportFileName;
use App\Utility\Export\FilesystemExportArchiveStore;
use App\Utility\Lock\Lock;
use App\Utility\Lock\LockName;
use App\Utility\Sanitize;
use App\Utility\TemplateRendererInterface;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Tests\TestFramework\Factory\CategoryFactory;
use Tests\TestFramework\Factory\EntryFactory;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;
use ZipArchive;

/**
 * @internal
 *
 * @coversNothing
 */
final class ExportRegressionTest extends IntegrationTestCase
{
    private string $directory;
    private MediaHelper $mediaHelper;
    private MediaService $media;
    private EntryExporter $exporter;

    protected function setUp() : void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/journal export ' . bin2hex(random_bytes(12));
        mkdir($this->directory, 0o700);
        $this->mediaHelper = new MediaHelper($this->directory . '/uploads');
        $this->media = new MediaService(new Encryptor(), $this->mediaHelper);
        $this->exporter = new EntryExporter($this->getRepository(EntryRepository::class), $this->media, $this->mediaHelper, new Sanitize(), new BladeRenderer(), $this->directory . '/exports');
    }

    protected function tearDown() : void
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    private function readArchive(string $path) : array
    {
        $archive = new ZipArchive();
        self::assertTrue($archive->open($path));
        $contents = [];

        for ($index = 0; $index < $archive->numFiles; ++$index) {
            $contents[$archive->getNameIndex($index)] = $archive->getFromIndex($index);
        }
        $archive->close();

        return $contents;
    }

    #[Test]
    public function deletion_waits_for_the_worker_and_removes_exports_before_an_account_id_can_be_reused() : void
    {
        $factory = UserFactory::setup();
        $original = $factory->create();
        $originalId = $original->getId();
        $username = $original->getUsername();
        $key = $factory->getDecryptionKey();
        EntryFactory::setup()->withUser($original)->withContent('<p>Original private content</p>', $key)->create();
        $management = $this->getService(UserManagementService::class);
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->expects(self::once())->method('render')->willReturnCallback(function (string $template, array $variables) use ($management, $original, $originalId) : string {
            try {
                $management->deleteUser($original);
                self::fail('Account deletion ran while its export worker was active');
            } catch (InvalidOperationException) {
                self::assertNotNull($this->testOrm->fetchOneAssoc('SELECT id FROM users WHERE id = ?', [$originalId]));
                self::assertNotEmpty($this->testOrm->fetchAllAssoc('SELECT id FROM entries WHERE userId = ?', [$originalId]));
            }

            return $variables['content'];
        });
        // Match ExportAllEntriesForUser.php, including its real shared OS lock.
        $worker = Lock::acquire(LockName::create($originalId, ExportFileName::prefix($username), LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER));
        $archive = null;

        try {
            $exporter = new EntryExporter($this->getRepository(EntryRepository::class), $this->media, $this->mediaHelper, new Sanitize(), $renderer);
            $archive = $exporter->export($originalId, $username, $key);
            self::assertNotNull($archive);
            self::assertFileExists($archive);
            $worker->unlock();
            $management->deleteUser($original);
            self::assertFileDoesNotExist($archive);
            self::assertNull($this->testOrm->fetchOneAssoc('SELECT id FROM users WHERE id = ?', [$originalId]));
            $replacement = UserFactory::setup()->create();
            $manager = $this->getService(EntityManagerInterface::class);
            // MySQL 5.7 can reuse the highest deleted ID after a restart.
            $manager->getConnection()->update('users', ['id' => $originalId], ['id' => $replacement->getId()]);
            $manager->clear();
            $exports = $this->getService(UserExportService::class);
            self::assertSame([], $exports->getZipFileNamesForExportedEntriesByUser($originalId));
            self::assertNull($exports->getZipFilePathForExportedEntriesByUser($originalId, basename($archive)));
        } finally {
            $worker->unlock();

            if ($archive !== null && is_file($archive)) {
                unlink($archive);
            }
            @rmdir(EXPORT_CACHE_PATH . '/' . $originalId);
        }
    }

    #[Test]
    public function a_late_export_from_a_deleted_account_is_not_inherited_when_its_username_is_reused() : void
    {
        $factory = UserFactory::setup();
        $original = $factory->create();
        $originalId = $original->getId();
        $key = $factory->getDecryptionKey();
        EntryFactory::setup()->withUser($original)->withContent('<p>Deleted account private text</p>', $key)->create();
        $replacement = null;
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->expects(self::once())->method('render')->willReturnCallback(function (string $template, array $variables) use ($original, &$replacement) : string {
            // The worker has decrypted a row, but has not published its ZIP yet.
            $this->getService(UserManagementService::class)->deleteUser($original);
            $replacement = UserFactory::setup()->create();

            return $variables['content'];
        });
        $exportPath = $this->directory . '/exports';
        $exporter = new EntryExporter($this->getRepository(EntryRepository::class), $this->media, $this->mediaHelper, new Sanitize(), $renderer, $exportPath);
        $archive = $exporter->export($originalId, $original->getUsername(), $key);
        self::assertNotNull($archive);
        self::assertNotNull($replacement);
        self::assertNotSame($originalId, $replacement->getId());
        self::assertStringContainsString('Deleted account private text', implode('', $this->readArchive($archive)));
        $service = new UserExportService($this->getService(UserHelper::class), $this->getRepository(EntryRepository::class), new FilesystemExportArchiveStore($exportPath), self::createStub(ExportJobRunner::class));
        self::assertSame([], $service->getZipFileNamesForExportedEntriesByUser($replacement->getId()));
        self::assertNull($service->getZipFilePathForExportedEntriesByUser($replacement->getId(), basename($archive)));
    }

    #[Test]
    public function entries_export_without_an_upload_directory_or_existing_export_directory() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        EntryFactory::setup()->withUser($user)->withContent('<p>No uploaded images</p>', $factory->getDecryptionKey())->create();
        self::assertDirectoryDoesNotExist($this->mediaHelper->getUserUploadDir($user->getId()));
        $path = $this->exporter->export($user->getId(), $user->getUsername(), $factory->getDecryptionKey());
        self::assertNotNull($path);
        $contents = $this->readArchive($path);
        self::assertCount(1, $contents);
        self::assertStringContainsString('<p>No uploaded images</p>', array_values($contents)[0]);
        self::assertSame(0o600, fileperms($path) & 0o777);
    }

    #[Test]
    public function colliding_entry_and_category_names_preserve_every_entry() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $first = CategoryFactory::setup()->withUser($user)->withName('Work!')->create();
        $second = CategoryFactory::setup()->withUser($user)->withName('Work?')->create();
        EntryFactory::setup()->withUser($user)->withCategory($first)->withTitle('Same title!')->withContent('<p>First</p>', $key)->create();
        EntryFactory::setup()->withUser($user)->withCategory($first)->withTitle('Same title?')->withContent('<p>Second</p>', $key)->create();
        EntryFactory::setup()->withUser($user)->withCategory($second)->withTitle('Same title!')->withContent('<p>Third</p>', $key)->create();
        $path = $this->exporter->export($user->getId(), $user->getUsername(), $key);
        self::assertNotNull($path);
        $contents = $this->readArchive($path);
        self::assertCount(3, $contents);

        foreach (['First', 'Second', 'Third'] as $expected) {
            self::assertCount(1, array_filter($contents, static fn ($html) => str_contains($html, '<p>' . $expected . '</p>')));
        }
    }

    #[Test]
    public function images_and_both_html_quote_styles_work_in_the_offline_export() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $temporary = tempnam($this->directory, 'image-');
        $this->media->encryptImage($user->getId(), new Image(base64_encode('image bytes'), 'pixel.png', 'png'), $key, $temporary);
        EntryFactory::setup()->withUser($user)->withContent('<img src="/media/pixel.png"><img src=\'/media/pixel.png\'>', $key)->create();
        $path = $this->exporter->export($user->getId(), $user->getUsername(), $key);
        self::assertNotNull($path);
        $contents = $this->readArchive($path);
        self::assertSame('image bytes', $contents['testuser/media/pixel.png']);
        $html = array_values(array_filter($contents, static fn ($name) => str_ends_with($name, '.html'), ARRAY_FILTER_USE_KEY))[0];
        $document = new DOMDocument();
        $document->loadHTML($html);
        $images = $document->getElementsByTagName('img');
        self::assertCount(2, $images);

        foreach ($images as $image) {
            self::assertSame('../media/pixel.png', $image->getAttribute('src'));
        }
    }

    #[Test]
    public function decryption_failure_leaves_no_partial_archive_or_plaintext_files() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        $temporary = tempnam($this->directory, 'image-');
        $this->media->encryptImage($user->getId(), new Image(base64_encode('private image bytes'), 'pixel.png', 'png'), $key, $temporary);
        EntryFactory::setup()->withUser($user)->withContent('<p>Private</p>', Key::createNewRandomKey())->create();

        try {
            $this->exporter->export($user->getId(), $user->getUsername(), $key);
            self::fail('An export was created with an incorrect key');
        } catch (WrongKeyOrModifiedCiphertextException) {
            self::assertSame([], array_values(array_diff(scandir($this->directory . '/exports/' . $user->getId()), ['.', '..'])));
            self::assertSame('private image bytes', $this->media->getDecryptedImage($user->getId(), 'pixel.png', $key)->getBinary());
        }
    }

    #[Test]
    public function usernames_with_hyphens_and_double_underscores_can_find_download_and_delete_their_archive() : void
    {
        $username = 'amy-jane__' . bin2hex(random_bytes(8));
        $user = UserFactory::setup()->withUsername($username)->create();
        $name = $username . '__01-01-2026_12-30-00.zip';
        $path = EXPORT_CACHE_PATH . '/' . $name;
        $otherName = $username . '__other__01-01-2026_12-30-00.zip';
        $otherPath = EXPORT_CACHE_PATH . '/' . $otherName;
        file_put_contents($path, 'synthetic archive');
        file_put_contents($otherPath, 'another account archive');

        try {
            $service = $this->getService(UserExportService::class);
            self::assertContains($name, $service->getZipFileNamesForExportedEntriesByUser($user->getId()));
            self::assertNotContains($otherName, $service->getZipFileNamesForExportedEntriesByUser($user->getId()));
            self::assertSame($path, $service->getZipFilePathForExportedEntriesByUser($user->getId(), $name));
            $service->deleteExportedEntriesZipFile($user->getId(), $name);
            self::assertFileDoesNotExist($path);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
            unlink($otherPath);
        }
    }

    #[Test]
    public function an_empty_account_reports_no_entries_instead_of_starting_a_job_that_produces_nothing() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $archives = $this->createMock(ExportArchiveStore::class);
        $archives->expects(self::never())->method('listForUser');
        $jobs = $this->createMock(ExportJobRunner::class);
        $jobs->expects(self::never())->method('start');
        $service = new UserExportService($this->getService(UserHelper::class), $this->getRepository(EntryRepository::class), $archives, $jobs);
        $this->expectException(ActionNotPermittedException::class);
        $this->expectExceptionMessage('There are no entries to export');
        $service->exportUserEntries($user->getId(), $factory->getDecryptionKey());
    }

    #[Test]
    public function an_export_can_use_a_different_job_runner_without_changing_its_policy() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        EntryFactory::setup()->withUser($user)->withContent('<p>Private</p>', $key)->create();
        $archives = $this->createMock(ExportArchiveStore::class);
        $archives->expects(self::once())->method('listForUser')->with($user->getId(), $user->getUsername())->willReturn([]);
        $jobs = $this->createMock(ExportJobRunner::class);
        $jobs->expects(self::once())->method('start')->with($user->getId(), $user->getUsername(), $key)->willReturn(42);
        $service = new UserExportService($this->getService(UserHelper::class), $this->getRepository(EntryRepository::class), $archives, $jobs);
        self::assertSame(42, $service->exportUserEntries($user->getId(), $key));
    }

    #[Test]
    public function an_existing_archive_prevents_starting_any_export_job() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        EntryFactory::setup()->withUser($user)->withContent('<p>Private</p>', $key)->create();
        $archives = $this->createMock(ExportArchiveStore::class);
        $archives->expects(self::once())->method('listForUser')->with($user->getId(), $user->getUsername())->willReturn(['existing.zip']);
        $jobs = $this->createMock(ExportJobRunner::class);
        $jobs->expects(self::never())->method('start');
        $service = new UserExportService($this->getService(UserHelper::class), $this->getRepository(EntryRepository::class), $archives, $jobs);
        $this->expectException(ActionNotPermittedException::class);
        $this->expectExceptionMessage('Allowed count of exports reached');
        $service->exportUserEntries($user->getId(), $key);
    }

    #[Test]
    public function renderer_failure_leaves_no_archive_or_plaintext_files() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        EntryFactory::setup()->withUser($user)->withContent('<p>Private</p>', $key)->create();
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->expects(self::once())->method('render')->with('export', ['content' => '<p>Private</p>'])->willThrowException(new RuntimeException('Renderer unavailable'));
        $exporter = new EntryExporter($this->getRepository(EntryRepository::class), $this->media, $this->mediaHelper, new Sanitize(), $renderer, $this->directory . '/exports');

        try {
            $exporter->export($user->getId(), $user->getUsername(), $key);
            self::fail('Export succeeded despite a rendering failure');
        } catch (RuntimeException $exception) {
            self::assertSame('Renderer unavailable', $exception->getMessage());
            self::assertSame([], array_values(array_diff(scandir($this->directory . '/exports/' . $user->getId()), ['.', '..'])));
        }
    }

    #[Test]
    public function an_export_can_use_a_renderer_without_blade_or_a_global_template_cache() : void
    {
        $factory = UserFactory::setup();
        $user = $factory->create();
        $key = $factory->getDecryptionKey();
        EntryFactory::setup()->withUser($user)->withContent('<p>Private</p>', $key)->create();
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer->expects(self::once())->method('render')->with('export', ['content' => '<p>Private</p>'])->willReturn('<html>Alternate renderer output</html>');
        $exporter = new EntryExporter($this->getRepository(EntryRepository::class), $this->media, $this->mediaHelper, new Sanitize(), $renderer, $this->directory . '/exports');
        $path = $exporter->export($user->getId(), $user->getUsername(), $key);
        self::assertNotNull($path);
        self::assertSame(['<html>Alternate renderer output</html>'], array_values($this->readArchive($path)));
    }
}
