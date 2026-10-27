<?php

declare(strict_types=1);

namespace Tests\Unit\Utility;

use App\Exception\UserException\InvalidOperationException;
use App\Utility\Export\BackgroundExportJobRunner;
use App\Utility\Export\ExportFileName;
use App\Utility\Lock\Lock;
use App\Utility\Lock\LockName;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal @coversNothing
 *
 * @coversNothing
 */
final class ExportDeletionLockTest extends TestCase
{
    public function test_cleanup_cannot_run_during_a_worker_and_holds_the_worker_lock_until_it_finishes() : void
    {
        $directory = sys_get_temp_dir() . '/journal-cleanup-' . bin2hex(random_bytes(12));
        mkdir($directory, 0o700);
        $runner = new BackgroundExportJobRunner(__FILE__, $directory, $directory);
        $worker = Lock::acquire(LockName::create(17, ExportFileName::prefix('a name'), LockName::ACTION_EXPORT_ALL_ENTRIES_FOR_USER), $directory);

        try {
            try {
                $runner->withIdleAccount(17, 'a name', static function () : void {
                    self::fail('Cleanup ran while a worker held the lock');
                });
                self::fail('Cleanup accepted a running export');
            } catch (InvalidOperationException) {
                self::assertTrue($runner->isRunning(17, 'a name'));
            }
            $worker->unlock();

            try {
                $runner->withIdleAccount(17, 'a name', static function () use ($runner) : void {
                    self::assertTrue($runner->isRunning(17, 'a name'));

                    throw new RuntimeException('Cleanup failed');
                });
                self::fail('Cleanup failure was hidden');
            } catch (RuntimeException $exception) {
                self::assertSame('Cleanup failed', $exception->getMessage());
            }
            self::assertFalse($runner->isRunning(17, 'a name'));
            $runner->withIdleAccount(17, 'a name', static function () use ($runner) : void {
                self::assertTrue($runner->isRunning(17, 'a name'));
            });
            self::assertFalse($runner->isRunning(17, 'a name'));
        } finally {
            $worker->unlock();

            foreach (glob($directory . '/*') as $file) {
                unlink($file);
            }
            rmdir($directory);
        }
    }
}
