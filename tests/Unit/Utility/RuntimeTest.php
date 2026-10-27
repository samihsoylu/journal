<?php

declare(strict_types=1);

namespace Tests\Unit\Utility;

use App\Framework\Environment;
use App\Utility\Command\Command;
use App\Utility\Command\Process;
use App\Utility\Sanitize;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RequiresOperatingSystemFamily;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** @internal
 * @coversNothing
 */
final class RuntimeTest extends TestCase
{
    #[Test]
    #[DataProvider('provideEnvironment_modes_are_distinctCases')]
    public function environment_modes_are_distinct(string $name) : void
    {
        $environment = Environment::from($name);
        self::assertSame($name, $environment->getValue());
        self::assertSame($name === 'dev', $environment->isDev());
        self::assertSame($name === 'test', $environment->isTest());
        self::assertSame($name === 'prod', $environment->isProd());
    }

    public static function provideEnvironment_modes_are_distinctCases() : iterable
    {
        return [['dev'], ['test'], ['prod']];
    }

    #[Test]
    public function unknown_environment_is_rejected() : void
    {
        $this->expectException(InvalidArgumentException::class);
        Environment::from('production');
    }

    #[Test]
    public function sanitizing_keeps_option_order_and_optional_query_values() : void
    {
        $sanitize = new Sanitize();
        self::assertSame('hello-world', $sanitize->string(' Hello World! ', [Sanitize::OPTION_TRIM, Sanitize::OPTION_LOWERCASE, Sanitize::OPTION_CLEAN_SPACES, Sanitize::OPTION_CLEAN_SPECIAL_CHARS]));
        self::assertSame('&lt;b&gt;Name&lt;/b&gt;', $sanitize->getVariable(['name' => ' <b>Name</b> '], 'name', Sanitize::TYPE_STRING));
        self::assertSame(-42, $sanitize->getVariable(['id' => 'id:-42'], 'id', Sanitize::TYPE_INT));
        self::assertNull($sanitize->getVariable([], 'id', Sanitize::TYPE_INT));
        self::assertNull($sanitize->getVariable(['id' => ''], 'id', Sanitize::TYPE_INT));
        self::assertNull($sanitize->getVariable(['id' => '42'], 'id', 'unknown'));
    }

    #[Test]
    public function unknown_sanitizer_option_is_rejected() : void
    {
        $this->expectException(LogicException::class);
        new Sanitize()->string('name', ['unknown']);
    }

    #[Test]
    public function command_arguments_are_literal_and_preserve_quotes() : void
    {
        $argument = "a name's `literal` \$HOME; echo injected";
        self::assertSame([$argument], new Command([PHP_BINARY], ['-r', 'echo $argv[1];', $argument])->execute());
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function failed_commands_report_the_exit_code() : void
    {
        define('SENTRY_ENABLED', false);
        $this->expectException(LogicException::class);
        $this->expectExceptionCode(7);
        new Command([PHP_BINARY], ['-r', 'exit(7);'])->execute();
    }

    #[Test]
    #[RequiresOperatingSystemFamily('Linux')]
    public function background_process_lookup_and_stop_only_affect_the_started_worker() : void
    {
        self::assertNull(Process::getById(2147483647));
        $worker = Process::start(new Command([PHP_BINARY], ['-r', 'sleep(30);']));

        try {
            self::assertTrue($worker->isRunning());
            self::assertSame($worker->getId(), Process::getById($worker->getId())?->getId());
            $worker->stop();
            $deadline = microtime(true) + 3;

            do {
                $state = @file_get_contents('/proc/' . $worker->getId() . '/stat');

                if ($state === false || preg_match('/\) Z /', $state)) {
                    break;
                }
                usleep(10000);
            } while (microtime(true) < $deadline);
            self::assertTrue($state === false || preg_match('/\) Z /', $state) === 1, 'Owned worker did not terminate');
        } finally {
            if ( ! isset($state) || ($state !== false && ! preg_match('/\) Z /', $state))) {
                $worker->stop();
            }
        }
    }
}
