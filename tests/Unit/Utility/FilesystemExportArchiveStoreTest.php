<?php

declare(strict_types=1);

namespace Tests\Unit\Utility;

use App\Exception\UserException\InvalidArgumentException;
use App\Exception\UserException\NotFoundException;
use App\Utility\Export\ExportFileName;
use App\Utility\Export\FilesystemExportArchiveStore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

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
        foreach (glob($this->directory . '/*') as $path) {
            unlink($path);
        }
        rmdir($this->directory);
    }

    #[Test]
    public function only_exact_owner_archives_are_listed() : void
    {
        $name = 'amy__jane__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/' . $name, 'own archive');
        file_put_contents($this->directory . '/amy__jane__other__01-01-2026_12-30-00.zip', 'foreign archive');
        file_put_contents($this->directory . '/amy__jane__unfinished.zip', 'partial archive');
        self::assertSame([$name], $this->store->listForUser('amy__jane'));
        self::assertSame([], $this->store->listForUser('missing-user'));
    }

    #[Test]
    public function special_characters_do_not_expand_globs_or_collide_with_other_users() : void
    {
        $name = '~616d795b315d__01-01-2026_12-30-00.zip'; // amy[1]
        $other = 'amy1__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/' . $name, 'own archive');
        file_put_contents($this->directory . '/' . $other, 'foreign archive');
        self::assertSame([$name], $this->store->listForUser('amy[1]'));
        self::assertSame($this->directory . '/' . $name, $this->store->findForUser('amy[1]', $name));
        $this->store->deleteForUser('amy[1]', $name);
        self::assertFileDoesNotExist($this->directory . '/' . $name);
        self::assertSame('foreign archive', file_get_contents($this->directory . '/' . $other));
    }

    #[Test]
    public function a_foreign_archive_cannot_be_found_or_deleted_for_another_user() : void
    {
        $name = 'bob__01-01-2026_12-30-00.zip';
        file_put_contents($this->directory . '/' . $name, 'foreign archive');
        self::assertNull($this->store->findForUser('alice', $name));

        try {
            $this->store->deleteForUser('alice', $name);
            self::fail('Deleted another account archive');
        } catch (NotFoundException) {
            self::assertSame('foreign archive', file_get_contents($this->directory . '/' . $name));
        }
    }

    #[Test]
    public function archive_paths_cannot_escape_the_store() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->store->findForUser('alice', '../bob__01-01-2026_12-30-00.zip');
    }
}
