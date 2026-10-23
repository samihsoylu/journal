<?php

declare(strict_types=1);

namespace Tests\Unit\Utility;

use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\NotFoundException;
use App\Utility\Export\ExportFileName;
use App\Utility\Export\FilesystemExportArchiveStore;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * @internal @coversNothing
 */
#[CoversClass(FilesystemExportArchiveStore::class)]
#[CoversClass(ExportFileName::class)]
final class FilesystemExportArchiveStoreTest extends TestCase
{
    private string $directory;
    private FilesystemExportArchiveStore $store;

    protected function setUp() : void
    {
        $this->directory = sys_get_temp_dir() . '/journal-archive-store-' . bin2hex(random_bytes(12));
        mkdir($this->directory, 0o700);
        $this->store = new FilesystemExportArchiveStore($this->directory);
    }

    protected function tearDown() : void
    {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
    }

    #[Test]
    public function an_unreadable_archive_directory_is_not_treated_as_an_empty_account() : void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            self::markTestSkipped('Filesystem permission checks require an unprivileged test process');
        }
        $name = 'alice__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/' . $name, 'private archive');
        chmod($this->directory, 0o100);

        try {
            // A known filename is readable with directory execute permission:
            // failed enumeration must not allow deletion to leave it behind.
            self::assertSame('private archive', file_get_contents($this->directory . '/' . $name));
            $this->expectException(RuntimeException::class);
            $this->store->listForUser(1, 'alice');
        } finally {
            chmod($this->directory, 0o700);
        }
    }

    #[Test]
    public function an_account_without_an_export_directory_still_lists_no_archives() : void
    {
        self::assertSame([], new FilesystemExportArchiveStore($this->directory . '/not-created')->listForUser(1, 'alice'));
    }

    #[Test]
    public function deletion_removes_duplicate_legacy_and_scoped_copies_of_the_same_archive() : void
    {
        mkdir($this->directory . '/1', 0o700);
        $name = 'alice__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/1/' . $name, 'scoped copy');
        file_put_contents($this->directory . '/' . $name, 'legacy copy');
        self::assertSame([$name], $this->store->listForUser(1, 'alice'));
        $this->store->deleteForUser(1, 'alice', $name);
        self::assertFileDoesNotExist($this->directory . '/1/' . $name);
        self::assertFileDoesNotExist($this->directory . '/' . $name);
        self::assertSame([], $this->store->listForUser(2, 'alice'));
    }

    #[Test]
    public function a_failed_unlink_is_reported_instead_of_allowing_account_deletion() : void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            self::markTestSkipped('Filesystem permission checks require an unprivileged test process');
        }
        $name = 'alice__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/' . $name, 'private archive');
        chmod($this->directory, 0o500);

        try {
            $this->expectException(RuntimeException::class);
            $this->store->deleteForUser(1, 'alice', $name);
        } finally {
            chmod($this->directory, 0o700);
        }
    }

    #[Test]
    public function account_ids_separate_reused_usernames_while_legacy_archives_remain_readable() : void
    {
        mkdir($this->directory . '/1', 0o700);
        mkdir($this->directory . '/2', 0o700);
        $old = 'alice__01-01-2026_12-30-00.zip';
        $current = 'alice__02-01-2026_12-30-00.zip';
        $legacy = 'alice__03-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/1/' . $old, 'deleted account private archive');
        self::assertSame([], $this->store->listForUser(2, 'alice'));
        self::assertNull($this->store->findForUser(2, 'alice', $old));

        try {
            $this->store->deleteForUser(2, 'alice', $old);
            self::fail('A replacement account deleted the original account archive');
        } catch (NotFoundException) {
            self::assertFileExists($this->directory . '/1/' . $old);
        }
        file_put_contents($this->directory . '/2/' . $current, 'current archive');
        file_put_contents($this->directory . '/' . $legacy, 'legacy archive');
        self::assertSame([$current, $legacy], $this->store->listForUser(2, 'alice'));
        self::assertSame($this->directory . '/2/' . $current, $this->store->findForUser(2, 'alice', $current));
        self::assertSame($this->directory . '/' . $legacy, $this->store->findForUser(2, 'alice', $legacy));
        $this->store->deleteForUser(2, 'alice', $current);
        $this->store->deleteForUser(2, 'alice', $legacy);
        self::assertSame([], $this->store->listForUser(2, 'alice'));
        self::assertFileExists($this->directory . '/1/' . $old);
    }

    #[Test]
    public function only_exact_owner_archives_are_listed() : void
    {
        $name = 'amy__jane__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/' . $name, 'own archive');
        file_put_contents($this->directory . '/amy__jane__other__01-01-2026_12-30-00.zip', 'foreign archive');
        file_put_contents($this->directory . '/amy__jane__unfinished.zip', 'partial archive');
        self::assertSame([$name], $this->store->listForUser(1, 'amy__jane'));
        self::assertSame([], $this->store->listForUser(1, 'missing-user'));
    }

    #[Test]
    public function special_characters_do_not_expand_globs_or_collide_with_other_users() : void
    {
        $name = '~616d795b315d__01-01-2026_12-30-00.zip'; // amy[1]
        $other = 'amy1__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/' . $name, 'own archive');
        file_put_contents($this->directory . '/' . $other, 'foreign archive');
        self::assertSame([$name], $this->store->listForUser(1, 'amy[1]'));
        self::assertSame($this->directory . '/' . $name, $this->store->findForUser(1, 'amy[1]', $name));
        $this->store->deleteForUser(1, 'amy[1]', $name);
        self::assertFileDoesNotExist($this->directory . '/' . $name);
        self::assertSame('foreign archive', file_get_contents($this->directory . '/' . $other));
    }

    #[Test]
    public function a_foreign_archive_cannot_be_found_or_deleted_for_another_user() : void
    {
        $name = 'bob__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/' . $name, 'foreign archive');
        self::assertNull($this->store->findForUser(1, 'alice', $name));

        try {
            $this->store->deleteForUser(1, 'alice', $name);
            self::fail('Deleted another account archive');
        } catch (NotFoundException) {
            self::assertSame('foreign archive', file_get_contents($this->directory . '/' . $name));
        }
    }

    #[Test]
    public function archive_paths_cannot_escape_the_store() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->store->findForUser(1, 'alice', '../bob__01-01-2026_12-30-00.zip');
    }
}
