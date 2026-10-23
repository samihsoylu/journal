<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Service\Security\RecoveryCodes;
use App\Service\Security\Totp;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/**
 * @internal
 *
 * @coversNothing
 */
final class TwoFactorPrimitivesTest extends TestCase
{
    #[DataProvider('provideRfc6238_sha1_vectors_and_adjacent_countersCases')]
    public function test_rfc6238_sha1_vectors_and_adjacent_counters(int $timestamp, string $code) : void
    {
        $clock = new MockClock(new DateTimeImmutable('@' . $timestamp));
        $totp = new Totp($clock);
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        self::assertSame(intdiv($timestamp, 30), $totp->match($secret, $code));
        $clock->sleep(30);
        self::assertSame(intdiv($timestamp, 30), $totp->match($secret, $code));
        $clock->sleep(30);
        self::assertNull($totp->match($secret, $code));
        self::assertNull($totp->match($secret, '１２３４５６'));
        self::assertNull($totp->match($secret, $code . '0'));
        self::assertNull($totp->match($secret, ' ' . $code));
    }

    public static function provideRfc6238_sha1_vectors_and_adjacent_countersCases() : iterable
    {
        return [[59, '287082'], [1111111109, '081804'], [1111111111, '050471'], [1234567890, '005924'], [2000000000, '279037'], [20000000000, '353130']];
    }

    public function test_generated_secrets_and_uri_are_local_authenticator_values() : void
    {
        $totp = new Totp(new MockClock());
        $secret = $totp->generateSecret();
        self::assertMatchesRegularExpression('/^[A-Z2-7]{32}$/', $secret);
        self::assertNotSame($secret, $totp->generateSecret());
        $uri = $totp->provisioningUri($secret, 'user:name');
        self::assertStringStartsWith('otpauth://totp/', $uri);
        parse_str(parse_url($uri, PHP_URL_QUERY), $query);
        self::assertSame($secret, $query['secret']);
        self::assertSame('Journal', $query['issuer']);
    }

    public function test_recovery_codes_have_128_random_bits_and_one_bounded_canonical_hash() : void
    {
        $policy = new RecoveryCodes();
        $codes = $policy->generate();
        self::assertCount(10, array_unique($codes));

        foreach ($codes as $code) {
            self::assertMatchesRegularExpression('/^[0-9a-f]{8}(?:-[0-9a-f]{8}){3}$/', $code);
            self::assertSame($policy->hash($code), $policy->hash(strtoupper(str_replace('-', ' ', $code))));
            self::assertNotSame($code, $policy->hash($code));
        }

        foreach (['', '123456', str_repeat('a', 129), str_repeat('ａ', 32), str_repeat('g', 32)] as $bad) {
            self::assertNull($policy->hash($bad));
        }
    }
}
