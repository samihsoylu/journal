<?php

declare(strict_types=1);

namespace Tests\Unit\Utility;

use App\Utility\Lock\Lock;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(Lock::class)]
final class LockTest extends TestCase
{
    private string $directory;

    protected function setUp() : void
    {
        $this->directory = sys_get_temp_dir() . '/journal-lock-' . bin2hex(random_bytes(12));
        mkdir($this->directory, 0o700);
    }

    protected function tearDown() : void
    {
        foreach (glob($this->directory . '/*') as $path) {
            unlink($path);
        }
        rmdir($this->directory);
    }

    #[Test]
    public function only_one_worker_can_hold_a_lock_and_release_allows_the_next_worker() : void
    {
        $name = 'journal-test-' . bin2hex(random_bytes(12));
        $first = null;
        $next = null;

        try {
            self::assertFalse(Lock::exists($name, $this->directory));
            $first = Lock::acquire($name, $this->directory);
            self::assertTrue(Lock::exists($name, $this->directory));

            try {
                Lock::acquire($name, $this->directory);
                self::fail('Two workers held the same lock');
            } catch (LogicException) {
                $first->unlock();
            }
            self::assertFalse(Lock::exists($name, $this->directory));
            $next = Lock::acquire($name, $this->directory);
            self::assertTrue(Lock::exists($name, $this->directory));
            $next->unlock();
            self::assertFalse(Lock::exists($name, $this->directory));
        } finally {
            unset($first, $next);

            if (is_file($this->directory . '/' . $name . '.lock')) {
                unlink($this->directory . '/' . $name . '.lock');
            }
        }
    }

    #[Test]
    public function an_abruptly_stopped_worker_does_not_leave_exports_permanently_locked() : void
    {
        $name = 'journal-test-' . bin2hex(random_bytes(12));
        $script = <<<'WORKER'
            define('CACHE_PATH', $argv[1]);
            require $argv[2];
            $lock = \App\Utility\Lock\Lock::acquire($argv[3]);
            fwrite(STDOUT, "ready\n");
            fflush(STDOUT);
            for ($i = 0; $i < 100; ++$i) usleep(100000);
            WORKER;
        $process = proc_open([PHP_BINARY, '-r', $script, $this->directory, BASE_PATH . '/vendor/autoload.php', $name], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        self::assertIsResource($process);
        fclose($pipes[0]);
        stream_set_timeout($pipes[1], 5);

        try {
            self::assertSame("ready\n", fgets($pipes[1]));
            self::assertTrue(Lock::exists($name, $this->directory));
            proc_terminate($process);
            proc_close($process);
            self::assertFalse(Lock::exists($name, $this->directory), 'A stopped export must not block the next export.');
            $next = Lock::acquire($name, $this->directory);
            $next->unlock();
            self::assertFalse(Lock::exists($name, $this->directory));
        } finally {
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }

            if (is_resource($pipes[1])) {
                fclose($pipes[1]);
            }

            if (is_resource($pipes[2])) {
                fclose($pipes[2]);
            }

            if (is_file($this->directory . '/' . $name . '.lock')) {
                unlink($this->directory . '/' . $name . '.lock');
            }
        }
    }
}
