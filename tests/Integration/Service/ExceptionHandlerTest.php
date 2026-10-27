<?php

declare(strict_types=1);

namespace Tests\Integration\Service;

use App\Utility\BladeRenderer;
use App\Utility\ExceptionHandler;
use App\Utility\Notification;
use App\Utility\Template;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 *
 * @coversNothing
 */
final class ExceptionHandlerTest extends TestCase
{
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function debug_mode_rethrows_the_original_exception() : void
    {
        define('BASE_URL', '');
        define('DEBUG_MODE', true);
        define('SITE_TITLE', 'Journal test');
        define('ASSETS_URL', '/assets');
        $_SERVER['REQUEST_URI'] = '/error-test';
        $_POST = $_SESSION = [];
        $renderer = new BladeRenderer();
        $exception = new RuntimeException('Synthetic diagnostic');
        $handler = new ExceptionHandler($exception, new Template($renderer, new Notification(), $renderer));
        self::assertSame($exception, $handler->getException());
        $this->expectExceptionObject($exception);
        $handler->genericException();
    }

    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function production_errors_without_sentry_leave_diagnostics_without_private_content() : void
    {
        define('BASE_URL', '');
        define('DEBUG_MODE', false);
        define('SENTRY_ENABLED', false);
        define('SITE_TITLE', 'Journal test');
        define('ASSETS_URL', '/assets');
        $_SERVER['REQUEST_URI'] = '/error-test';
        $_POST = [];
        $_SESSION = [];
        $_COOKIE = [];
        $directory = sys_get_temp_dir() . '/journal-errors-' . bin2hex(random_bytes(12));
        mkdir($directory, 0o700);
        $path = $directory . '/errors.log';
        $previousLogPath = ini_set('error_log', $path);
        $previousLogging = ini_set('log_errors', '0');
        $privateMessage = 'Synthetic private entry and password';
        $outputLevel = ob_get_level();

        try {
            ob_start();
            $renderer = new BladeRenderer();
            new ExceptionHandler(new RuntimeException($privateMessage), new Template($renderer, new Notification(), $renderer))->genericException();
            $page = ob_get_clean();
            self::assertSame(500, http_response_code());
            self::assertStringContainsString('Internal Server Error', $page);
            self::assertStringNotContainsString($privateMessage, $page);
            self::assertFileExists($path);
            $log = file_get_contents($path);
            self::assertStringContainsString(RuntimeException::class, $log);
            self::assertStringNotContainsString($privateMessage, $log);
        } finally {
            while (ob_get_level() > $outputLevel) {
                ob_end_clean();
            }
            ini_set('error_log', $previousLogPath);
            ini_set('log_errors', $previousLogging);
            http_response_code(200);

            if (is_file($path)) {
                unlink($path);
            }
            rmdir($directory);
        }
    }
}
