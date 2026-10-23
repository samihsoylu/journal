<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Utility\UserSession;
use PHPUnit\Framework\TestCase;

/**
 * @internal @coversNothing
 *
 * @coversNothing
 */
final class CsrfTokenTest extends TestCase
{
    public function test_tokens_have_256_bits_and_change_even_for_the_same_session() : void
    {
        $first = UserSession::generateNewAntiCSRFToken('same-session');
        $second = UserSession::generateNewAntiCSRFToken('same-session');
        self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $first);
        self::assertMatchesRegularExpression('/\A[a-f0-9]{64}\z/', $second);
        self::assertNotSame($first, $second);
    }
}
