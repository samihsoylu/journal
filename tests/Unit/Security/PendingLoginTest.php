<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use App\Exception\UserException\InvalidParameterException;
use App\Service\Security\PendingLogin;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

/**
 * @internal
 *
 * @coversNothing
 */
#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class PendingLoginTest extends TestCase
{
    protected function setUp() : void
    {
        session_start();
        $_SESSION = [];
        $_COOKIE = [];
    }

    public function test_pending_state_binds_cookie_and_csrf_without_storing_the_key_or_granting_a_session() : void
    {
        $clock = new MockClock();
        $pending = new PendingLogin($clock);
        $before = session_id();
        $pending->begin(7, 'verified-fingerprint', 'private-unlocked-key');
        self::assertNotSame($before, session_id());
        self::assertStringNotContainsString('private-unlocked-key', serialize($_SESSION));
        self::assertArrayNotHasKey('SessionID', $_SESSION);
        self::assertArrayNotHasKey('EEK', $_COOKIE);
        $_COOKIE[PendingLogin::COOKIE] = 'private-unlocked-key';
        $state = $pending->state();
        self::assertSame(7, $state['userId']);
        self::assertSame($state, $pending->requireState($state['csrf']));
        $clock->sleep(299);
        self::assertSame($state['expires'], $pending->state()['expires']);
        $clock->sleep(1);
        self::assertNull($pending->state());
        self::assertArrayNotHasKey('pending_login', $_SESSION);
        self::assertArrayNotHasKey(PendingLogin::COOKIE, $_COOKIE);
    }

    public function test_wrong_cookie_and_malformed_state_fail_closed() : void
    {
        $pending = new PendingLogin(new MockClock());

        foreach ([null, [], 'different-key', str_repeat('x', 1025)] as $cookie) {
            $pending->begin(7, 'fingerprint', 'private-key');
            $_COOKIE[PendingLogin::COOKIE] = $cookie;
            self::assertNull($pending->state());
        }
        $_SESSION['pending_login'] = ['userId' => [], 'expires' => 'tomorrow'];
        self::assertNull($pending->state());
    }

    public function test_malformed_csrf_never_reaches_key_use() : void
    {
        $pending = new PendingLogin(new MockClock());
        $pending->begin(7, 'fingerprint', 'private-key');
        $_COOKIE[PendingLogin::COOKIE] = 'private-key';
        $this->expectException(InvalidParameterException::class);
        $pending->requireState([]);
    }
}
