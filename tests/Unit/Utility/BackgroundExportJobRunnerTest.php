<?php

declare(strict_types=1);

namespace Tests\Unit\Utility;

use App\Utility\Command\Command;
use App\Utility\Command\Process;
use App\Utility\Export\BackgroundExportJobRunner;
use Defuse\Crypto\Key;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(BackgroundExportJobRunner::class)]
#[CoversClass(Process::class)]
#[CoversClass(Command::class)]
#[RequiresOperatingSystemFamily('Linux')]
final class BackgroundExportJobRunnerTest extends TestCase
{
    #[Test]
    public function the_worker_receives_the_key_through_a_pipe_and_starts_without_waiting_for_completion() : void
    {
        $directory = sys_get_temp_dir() . '/journal worker ' . bin2hex(random_bytes(12));
        mkdir($directory, 0o700);
        $script = $directory . '/worker.php';
        file_put_contents($script, <<<'WORKER'
            <?php
            $input = stream_get_contents(STDIN);
            file_put_contents(__DIR__ . '/received.json', json_encode(['argv' => $argv, 'digest' => hash('sha256', $input)]));
            for ($i = 0; $i < 500 && !is_file(__DIR__ . '/release'); ++$i) usleep(10000);
            file_put_contents(__DIR__ . '/finished', 'done');
            WORKER);
        $key = Key::createNewRandomKey();

        try {
            $pid = new BackgroundExportJobRunner($script, $directory)->start(12345, "a name's spaces", $key);
            self::assertGreaterThan(0, $pid);

            for ($i = 0; $i < 200 && ! is_file($directory . '/received.json'); ++$i) {
                usleep(10000);
            }
            self::assertFileExists($directory . '/received.json');
            self::assertFileDoesNotExist($directory . '/finished', 'The web request must not wait for the export to finish.');
            $received = json_decode(file_get_contents($directory . '/received.json'), true, 512, JSON_THROW_ON_ERROR);
            self::assertSame([$script, '12345', "a name's spaces"], $received['argv']);
            self::assertSame(hash('sha256', $key->saveToAsciiSafeString()), $received['digest']);
            self::assertStringNotContainsString($key->saveToAsciiSafeString(), file_get_contents('/proc/' . $pid . '/cmdline'));
            self::assertFileExists($directory . '/~' . bin2hex("a name's spaces") . '.log');
        } finally {
            touch($directory . '/release');

            for ($i = 0; $i < 200 && ! is_file($directory . '/finished'); ++$i) {
                usleep(10000);
            }

            foreach (glob($directory . '/*') as $path) {
                unlink($path);
            }
            rmdir($directory);
        }
    }
}
