<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Service\Security\FilesystemAttemptStorage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\RateLimiter\Policy\Window;

/** @internal
 * @coversNothing
 */
final class AttemptStorageTest extends TestCase
{
    private string $directory;

    protected function setUp() : void
    {
        $this->directory = sys_get_temp_dir() . '/journal-storage-' . bin2hex(random_bytes(12));
        mkdir($this->directory, 0o700);
    }

    protected function tearDown() : void
    {
        chmod($this->directory, 0o700);

        foreach (glob($this->directory . '/*') as $file) {
            is_dir($file) ? rmdir($file) : unlink($file);
        }
        rmdir($this->directory);
    }

    #[Test]
    #[DataProvider('provideInvalid_or_foreign_state_never_grants_a_fresh_budgetCases')]
    public function invalid_or_foreign_state_never_grants_a_fresh_budget(string $payload) : void
    {
        file_put_contents($this->directory . '/' . hash('sha256', 'account') . '.state', $payload);
        $this->expectException(RuntimeException::class);
        new FilesystemAttemptStorage($this->directory)->fetch('account');
    }

    public static function provideInvalid_or_foreign_state_never_grants_a_fresh_budgetCases() : iterable
    {
        yield 'oversized' => [str_repeat('x', 65537)];
        yield 'not serialized' => ['broken'];
        yield 'foreign identity' => [serialize([null, new Window('other-account', 60, 10)])];
        yield 'invalid expiry type' => [serialize(['tomorrow', new Window('account', 60, 10)])];
        yield 'unknown serialized class' => [serialize([null, new stdClass()])];
    }

    #[Test]
    public function expired_state_is_empty_but_unexpired_state_retains_hits() : void
    {
        $storage = new FilesystemAttemptStorage($this->directory);
        $window = new Window('account', 60, 10);
        $window->add(4);
        $storage->save($window);
        $restored = $storage->fetch('account');
        self::assertInstanceOf(Window::class, $restored);
        self::assertSame(4, $restored->getHitCount());
        $path = $this->directory . '/' . hash('sha256', 'account') . '.state';
        file_put_contents($path, serialize([microtime(true) - 1.0, $window]));
        self::assertNull($storage->fetch('account'));
        $storage->delete('account');
        self::assertFileDoesNotExist($path);
    }

    #[Test]
    public function an_existing_file_cannot_be_used_as_the_storage_directory() : void
    {
        file_put_contents($this->directory . '/file', 'occupied');
        $this->expectException(RuntimeException::class);
        new FilesystemAttemptStorage($this->directory . '/file/child');
    }

    #[Test]
    public function publication_failure_leaves_no_temporary_state() : void
    {
        mkdir($this->directory . '/' . hash('sha256', 'account') . '.state');

        try {
            new FilesystemAttemptStorage($this->directory)->save(new Window('account', 60, 10));
            self::fail('State was written over a directory');
        } catch (RuntimeException) {
            self::assertSame([], glob($this->directory . '/.attempt-*'));
        }
    }

    #[Test]
    public function unreadable_state_cannot_be_ignored_or_reset() : void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            self::markTestSkipped('Filesystem permission checks require an unprivileged test process');
        }
        $storage = new FilesystemAttemptStorage($this->directory);
        $storage->save(new Window('account', 60, 10));
        $path = $this->directory . '/' . hash('sha256', 'account') . '.state';
        chmod($path, 0o000);

        try {
            $this->expectException(RuntimeException::class);
            $storage->fetch('account');
        } finally {
            chmod($path, 0o600);
        }
    }
}
