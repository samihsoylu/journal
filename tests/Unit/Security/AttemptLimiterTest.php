<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Exception\UserException\InvalidOperationException;
use App\Service\Security\AttemptLimiter;
use FilesystemIterator;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/** @internal */
#[CoversClass(AttemptLimiter::class)]
final class AttemptLimiterTest extends TestCase
{
    private string $directory;

    protected function setUp() : void
    {
        $this->directory = sys_get_temp_dir() . '/journal-attempts-' . bin2hex(random_bytes(12));
    }

    protected function tearDown() : void
    {
        if ( ! is_dir($this->directory)) {
            return;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
    }

    #[Test]
    public function a_limit_survives_new_instances_and_other_identities_reset_independently() : void
    {
        $limiter = new AttemptLimiter($this->directory);

        for ($i = 0; $i < 10; ++$i) {
            $limiter->reserve('login', 'account-a');
        }
        $limiter->reserve('login', 'account-b');
        $limiter->reset('login', 'account-b');

        try {
            new AttemptLimiter($this->directory)->reserve('login', 'account-a');
            self::fail('A new instance or another account reset the original limit');
        } catch (InvalidOperationException) {
            $limiter->reset('login', 'account-a');
        }
        $limiter->reserve('login', 'account-a');
        $this->addToAssertionCount(1);
    }

    #[Test]
    public function unknown_purposes_fail_closed() : void
    {
        $this->expectException(LogicException::class);
        new AttemptLimiter($this->directory)->reserve('unknown', 'account');
    }

    #[Test]
    public function unwritable_state_fails_closed_even_when_locks_remain_writable() : void
    {
        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            self::markTestSkipped('Filesystem permission checks require an unprivileged test process');
        }
        $limiter = new AttemptLimiter($this->directory);
        $limiter->reserve('two-factor-account', 'account');
        $state = $this->directory . '/state';
        chmod($state, 0o500);

        try {
            $this->expectException(RuntimeException::class);
            $limiter->reserve('two-factor-account', 'account');
        } finally {
            chmod($state, 0o700);
        }
    }

    #[Test]
    public function corrupt_state_is_rejected_instead_of_becoming_a_new_budget() : void
    {
        $limiter = new AttemptLimiter($this->directory);
        $limiter->reserve('two-factor-account', 'account');
        $files = glob($this->directory . '/state/*.state');
        self::assertCount(1, $files);
        file_put_contents($files[0], 'corrupt');
        $this->expectException(RuntimeException::class);
        $limiter->reserve('two-factor-account', 'account');
    }

    #[Test]
    public function parallel_php_processes_cannot_overbook_the_attempt_budget() : void
    {
        $script = <<<'WORKER'
            require $argv[1];
            try {
                new \App\Service\Security\AttemptLimiter($argv[2])->reserve('two-factor-account', 'same-account');
                echo 'allowed';
            } catch (\App\Exception\UserException\InvalidOperationException) {
                echo 'denied';
            }
            WORKER;
        $workers = [];

        try {
            for ($i = 0; $i < 24; ++$i) {
                $process = proc_open([PHP_BINARY, '-r', $script, BASE_PATH . '/vendor/autoload.php', $this->directory], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
                self::assertIsResource($process);
                fclose($pipes[0]);
                $workers[] = [$process, $pipes];
            }
            $allowed = $denied = 0;

            foreach ($workers as [$process,$pipes]) {
                $result = stream_get_contents($pipes[1]);
                $errors = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                self::assertSame(0, proc_close($process), $errors);
                $allowed += $result === 'allowed' ? 1 : 0;
                $denied += $result === 'denied' ? 1 : 0;
            }
            self::assertSame(10, $allowed);
            self::assertSame(14, $denied);
        } finally {
            foreach ($workers as [$process,$pipes]) {
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
            }
        }
    }
}
