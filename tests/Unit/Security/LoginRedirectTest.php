<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Utility\Redirect;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal @coversNothing
 *
 * @coversNothing
 */
final class LoginRedirectTest extends TestCase
{
    #[DataProvider('provideLogin_redirect_rejects_external_or_malformed_destinationsCases')]
    public function test_login_redirect_rejects_external_or_malformed_destinations(mixed $destination) : void
    {
        self::assertSame('/journal/dashboard', Redirect::localDestination($destination, '/journal/dashboard'));
    }

    public static function provideLogin_redirect_rejects_external_or_malformed_destinationsCases() : iterable
    {
        foreach ([null, [], '', 'https://outside.invalid', '//outside.invalid', '///outside.invalid', '/\outside.invalid', '/path\other', "/entries\r\nLocation: https://outside.invalid", '/path with spaces'] as $value) {
            yield [$value];
        }
    }

    public function test_login_redirect_keeps_local_paths_and_subdirectory_deployments() : void
    {
        self::assertSame('/entries', Redirect::localDestination('/entries', '/dashboard'));
        self::assertSame('/journal/entries', Redirect::localDestination('/journal/entries', '/journal/dashboard'));
    }
}
