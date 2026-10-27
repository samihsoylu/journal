<?php

declare(strict_types=1);

namespace Journal\Checks;

function authenticatorCode(string $secret, int $offset = 0) : string
{
    // Independent RFC6238 reference for E2E; the test host needs no packages.
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $buffer = $bits = 0;
    $decoded = '';

    foreach (str_split($secret) as $character) {
        $value = strpos($alphabet, $character);
        ensure($value !== false, 'Invalid authenticator setup key');
        $buffer = ($buffer << 5) | $value;
        $bits += 5;

        if ($bits >= 8) {
            $bits -= 8;
            $decoded .= chr(($buffer >> $bits) & 255);
            $buffer &= (1 << $bits) - 1;
        }
    }
    $counter = intdiv(time() + $offset, 30);
    $digest = hash_hmac('sha1', pack('N2', intdiv($counter, 4294967296), $counter & 0xFFFFFFFF), $decoded, true);
    $position = ord($digest[19]) & 15;
    $value = unpack('N', substr($digest, $position, 4))[1] & 0x7FFFFFFF;

    return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
}

function recoveryCodes(string $body) : array
{
    ensure(preg_match('/<ul id="recovery-codes"[^>]*>(.*?)<\/ul>/s', $body, $list) === 1, 'Missing recovery-code display');
    preg_match_all('/<code>([0-9a-f-]{35})<\/code>/', $list[1], $codes);
    ensure(count($codes[1]) === 10 && count(array_unique($codes[1])) === 10, 'Recovery codes are incomplete');

    return $codes[1];
}

function verifyTwoFactorRequests(Browser $owner, callable $sql, string $temporary, string $container) : void
{
    $post = static fn (Browser $client, string $page, string $action, array $fields) => $client->request($action, ['form_key' => Browser::token($client->page($page)), ...$fields]);
    $post($owner, '/user/create', '/user/create/action', ['username' => 'factor-user', 'password' => 'factor-user-password', 'email' => 'factor@example.test', 'privilegeLevel' => 3]);
    $id = $sql("SELECT id FROM journal.users WHERE username='factor-user'");
    ensure(ctype_digit($id), 'Could not create authenticator test account');
    $client = new Browser($owner->base);
    $client->login('factor-user', 'factor-user-password');
    $dormant = new Browser($owner->base);
    $dormant->login('factor-user', 'factor-user-password');

    foreach (['begin', 'confirm', 'cancel', 'disable', 'regenerate'] as $action) {
        ensure(in_array($client->request('/account/security/' . $action)[0], [404, 405], true), 'Security settings accept GET mutations');
        ensure($client->request('/account/security/' . $action, ['form_key' => ['malformed']])[0] === 200, 'Malformed security form should render a validation error');
    }
    $post($client, '/account/security', '/account/security/begin', ['password' => ['malformed']]);
    $post($client, '/account/security', '/account/security/begin', ['password' => 'wrong-password']);
    ensure($sql('SELECT COUNT(*) FROM journal.user_two_factor WHERE userId=' . $id) === '0', 'Bad setup enrolled a user');
    $post($client, '/account/security', '/account/security/begin', ['password' => 'factor-user-password']);
    ensure($sql('SELECT COUNT(*) FROM journal.user_two_factor WHERE userId=' . $id) === '0', 'Unconfirmed setup enrolled a user');
    $post($client, '/account/security', '/account/security/cancel', []);
    ensure( ! str_contains($client->page('/account/security'), 'id="authenticator-secret"'), 'Cancelling retained setup');
    $post($client, '/account/security', '/account/security/begin', ['password' => 'factor-user-password']);
    $setup = $client->page('/account/security');
    ensure(preg_match('/id="authenticator-secret"[^>]*>([A-Z2-7]+)<\/code>/', $setup, $secret) === 1, 'Missing local setup key');
    $secret = $secret[1];
    $post($client, '/account/security', '/account/security/confirm', ['code' => 'not-a-code']);
    $enrollmentCode = authenticatorCode($secret, -30);
    [$status, $body] = $post($client, '/account/security', '/account/security/confirm', ['code' => $enrollmentCode]);
    ensure($status === 200, 'Confirmation failed');
    $codes = recoveryCodes($body);
    ensure(str_contains($client->headers['cache-control'] ?? '', 'no-store'), 'Recovery response may be cached');
    ensure( ! str_contains($client->page('/account/security'), $codes[0]), 'Recovery codes are shown again');
    $state = $sql('SELECT encryptedSecret FROM journal.user_two_factor WHERE userId=' . $id);
    ensure($state !== $secret && ! str_contains($state, $secret), 'Authenticator secret stored as plaintext');
    ensure($sql('SELECT COUNT(*) FROM journal.user_two_factor_recovery WHERE userId=' . $id) === '10', 'Recovery hashes missing');
    $challenge = new Browser($owner->base);
    [$status, $body, $location] = $challenge->request('/login/action', ['form_key' => Browser::token($challenge->page('/login')), 'username' => 'factor-user', 'password' => 'factor-user-password']);
    ensure($status === 200 && str_ends_with($location, '/login/two-factor'), 'Password bypassed second factor');
    $cookies = $challenge->cookies();
    ensure( ! isset($cookies['EEK']) && isset($cookies['JournalPendingKey']), 'Pending login received authenticated encryption cookie');
    $session = run(['docker', 'exec', $container, 'php', '-r', 'echo file_get_contents((session_save_path() ?: sys_get_temp_dir()) . "/sess_" . $argv[1]);', $cookies['PHPSESSID']]);
    ensure( ! str_contains($session, 'SessionID|') && ! str_contains($session, $cookies['JournalPendingKey']) && ! str_contains($session, 'factor-user-password'), 'Pending session contains authenticated identity or secret key');

    foreach (['/dashboard', '/entries', '/account', '/account/security', '/users', '/media/missing.png', '/template/1/ajax', '/account/export-entries/download/missing.zip'] as $path) {
        [$code, , $redirect] = $challenge->request($path);
        ensure($code !== 500 && str_ends_with($redirect, '/login/two-factor'), 'Pending login accessed private path: ' . $path);
    }
    [$status] = $post($challenge, '/login/two-factor', '/login/two-factor', ['code' => ['malformed']]);
    ensure($status === 200, 'Malformed factor code crashed');
    $post($challenge, '/login/two-factor', '/login/two-factor', ['code' => $enrollmentCode]);
    ensure( ! isset($challenge->cookies()['EEK']), 'Enrollment code was replayed');
    $loginCode = authenticatorCode($secret);
    [$status, , $location] = $post($challenge, '/login/two-factor', '/login/two-factor', ['code' => $loginCode]);
    ensure($status !== 500 && isset($challenge->cookies()['EEK']), 'Fresh authenticator login failed');
    ensure(str_contains($challenge->page('/account/security'), 'Status: enabled'), 'Completed factor cannot reach account settings');
    $post($challenge, '/account/security', '/logout', []);
    $challenge->request('/login/action', ['form_key' => Browser::token($challenge->page('/login')), 'username' => 'factor-user', 'password' => 'factor-user-password']);
    $post($challenge, '/login/two-factor', '/login/two-factor', ['code' => $loginCode]);
    ensure( ! isset($challenge->cookies()['EEK']), 'A used login code was replayed');
    $post($challenge, '/login/two-factor', '/login/two-factor/cancel', []);
    ensure( ! isset($challenge->cookies()['JournalPendingKey']), 'Cancelling left the pending key cookie');
    $challenge->request('/login/action', ['form_key' => Browser::token($challenge->page('/login')), 'username' => 'factor-user', 'password' => 'factor-user-password']);
    $post($challenge, '/login/two-factor', '/login/two-factor', ['code' => $codes[0]]);
    ensure(isset($challenge->cookies()['EEK']), 'Recovery login failed');
    $post($challenge, '/account', '/account/change-password', ['currentPassword' => 'factor-user-password', 'newPassword' => 'new-factor-password', 'confirmPassword' => 'new-factor-password']);
    $challenge->request('/login/action', ['form_key' => Browser::token($challenge->page('/login')), 'username' => 'factor-user', 'password' => 'new-factor-password']);
    $post($challenge, '/login/two-factor', '/login/two-factor', ['code' => $codes[0]]);
    ensure( ! isset($challenge->cookies()['EEK']), 'A recovery code was reused after changing password');
    $post($challenge, '/login/two-factor', '/login/two-factor', ['code' => $codes[1]]);
    ensure(isset($challenge->cookies()['EEK']), 'Recovery login after password change failed');
    [$status, $body] = $post($challenge, '/account/security', '/account/security/regenerate', ['password' => 'new-factor-password', 'code' => $codes[2]]);
    $replacement = recoveryCodes($body);
    run(['docker', 'cp', ROOT . '/ci/verify-two-factor-store.php', $container . ':/tmp/verify-two-factor-store.php']);
    $proof = run(['docker', 'exec', '-i', '--user', 'www-data', $container, 'php', '/tmp/verify-two-factor-store.php', $id], $replacement[8]);
    ensure(str_contains($proof, 'Concurrent factor consumption: passed'), 'Concurrent factor storage check failed');
    echo $proof;
    $post($challenge, '/account/security', '/account/security/disable', ['password' => 'new-factor-password', 'code' => $codes[3]]);
    ensure(str_contains($challenge->page('/account/security'), 'Status: enabled'), 'Regeneration did not replace old codes');
    $post($challenge, '/account/security', '/account/security/disable', ['password' => 'new-factor-password', 'code' => $replacement[0]]);
    ensure(str_contains($challenge->page('/account/security'), 'Status: disabled'), 'Disabling failed');
    ensure(str_contains($dormant->request('/entries')[2], '/login'), 'Enable/disable revived a dormant password-only session');
    $revision = $sql('SELECT revision FROM journal.user_two_factor WHERE userId=' . $id);
    ensure(strlen($revision) === 64 && $sql('SELECT encryptedSecret IS NULL FROM journal.user_two_factor WHERE userId=' . $id) === '1', 'Disabled revision was not retained');
    $post($owner, '/user/' . $id, '/user/' . $id . '/delete', []);
    ensure($sql('SELECT COUNT(*) FROM journal.user_two_factor WHERE userId=' . $id) === '0' && $sql('SELECT COUNT(*) FROM journal.user_two_factor_recovery WHERE userId=' . $id) === '0', 'Deleted users retained authentication data');
    echo "Two-factor HTTP: enrollment, private pending login, replay, recovery, password change, regeneration, disable, dormant-session revocation and deletion passed\n";
}

function verifyTwoFactorBrowser(EditorBrowser $browser, string $base) : void
{
    $browser->visit($base . '/account/security');
    $browser->script("document.querySelector('#setup-password').value = 'fresh-password'; document.querySelector('form[action$=\"/begin\"]').requestSubmit();");
    $secret = waitFor(static fn () => $browser->script("return document.querySelector('#authenticator-secret')?.textContent || false;"), 'authenticator setup key', 15);
    $browser->script("document.querySelector('#setup-code').value = arguments[0]; document.querySelector('form[action$=\"/confirm\"]').requestSubmit();", [authenticatorCode($secret, -30)]);
    $codes = waitFor(static fn () => $browser->script("return document.querySelector('#recovery-codes') ? [...document.querySelectorAll('#recovery-codes code')].map(code => code.textContent) : false;"), 'browser recovery codes', 15);
    ensure(count($codes) === 10, 'Browser recovery display incomplete');
    $browser->script("document.querySelector('a[href$=\"/account/security\"]').click();");
    waitFor(static fn () => $browser->script("return document.querySelector('#two-factor-status')?.textContent.includes('enabled');"), 'confirmed security settings', 15);
    ensure( ! $browser->script('return document.body.textContent.includes(arguments[0]);', [$codes[0]]), 'Browser displayed recovery codes again');
    $browser->script("document.querySelector('#logout-form').requestSubmit();");
    waitFor(static fn () => $browser->script("return location.pathname === '/login';"), 'browser logout', 15);
    $browser->script("document.querySelector('#username').value = 'freshowner'; document.querySelector('#password').value = 'fresh-password'; document.querySelector('form').requestSubmit();");
    waitFor(static fn () => $browser->script("return location.pathname === '/login/two-factor';"), 'browser factor challenge', 15);
    $cookies = $browser->cookies();
    ensure( ! array_any($cookies, static fn ($cookie) => $cookie['name'] === 'EEK'), 'Password stage set authenticated cookie');
    $pending = array_values(array_filter($cookies, static fn ($cookie) => $cookie['name'] === 'JournalPendingKey'));
    ensure(count($pending) === 1 && $pending[0]['httpOnly'] && $pending[0]['sameSite'] === 'Strict' && ! $pending[0]['secure'], 'Pending cookie flags missing');
    $browser->script("document.querySelector('#code').value = arguments[0]; document.querySelector('form[action$=\"/two-factor\"]').requestSubmit();", [authenticatorCode($secret)]);
    waitFor(static fn () => $browser->script("return location.pathname === '/dashboard';"), 'browser two-factor completion', 15);
    $browser->visit($base . '/account/security');
    $browser->script("document.querySelector('#security-password').value = 'fresh-password'; document.querySelector('#security-code').value = arguments[0]; document.querySelector('form[action$=\"/regenerate\"]').requestSubmit();", [$codes[0]]);
    $replacement = waitFor(static fn () => $browser->script("return document.querySelector('#recovery-codes') ? [...document.querySelectorAll('#recovery-codes code')].map(code => code.textContent) : false;"), 'regenerated browser recovery codes', 15);
    ensure(count($replacement) === 10 && $replacement !== $codes, 'Browser recovery codes were not replaced');
    $browser->visit($base . '/account/security');
    $browser->script("document.querySelector('#security-password').value = 'fresh-password'; document.querySelector('#security-code').value = arguments[0]; document.querySelector('button[formaction$=\"/disable\"]').click();", [$replacement[0]]);
    waitFor(static fn () => $browser->script("return document.querySelector('#two-factor-status')?.textContent.includes('disabled');"), 'browser disabling', 15);
    $browser->visit($base . '/account');
    $browser->script("document.querySelector('[data-target=\"account_menu\"]').click();");
    waitFor(static fn () => $browser->script("return M.Dropdown.getInstance(document.querySelector('[data-target=\"account_menu\"]')).isOpen;"), 'desktop account menu', 10);
    $browser->script("M.Dropdown.getInstance(document.querySelector('[data-target=\"account_menu\"]')).close();");
    $browser->cdp('Emulation.setDeviceMetricsOverride', ['width' => 390, 'height' => 844, 'deviceScaleFactor' => 1, 'mobile' => true]);
    $browser->script("document.querySelector('.sidenav-trigger').click();");
    waitFor(static fn () => $browser->script("return M.Sidenav.getInstance(document.querySelector('#mobile_menu')).isOpen;"), 'mobile navigation', 10);
    $browser->script("document.querySelector('#mobile_menu a[href$=\"/entries\"]').click();");
    waitFor(static fn () => $browser->script("return location.pathname === '/entries';"), 'mobile entries navigation', 15);
    $browser->cdp('Emulation.clearDeviceMetricsOverride');
    echo "Two-factor browser: enrollment, private challenge/cookie flags, authenticator login, one-time recovery display, regeneration and disabling passed; desktop/mobile menus work\n";
}
