<?php

declare(strict_types=1);

namespace Journal\Checks;

use ZipArchive;

function verifyAccountRequests(Browser $owner, callable $sql, string $temporary) : void
{
    $post = static fn (Browser $browser, string $action, array $fields) : array => $browser->request($action, ['form_key' => Browser::token($browser->page('/account')), ...$fields]);
    $owner->request('/user/create/action', ['form_key' => Browser::token($owner->page('/user/create')), 'username' => 'account-reader', 'password' => 'account-password', 'email' => 'account@example.test', 'privilegeLevel' => 3]);
    $id = $sql("SELECT id FROM journal.users WHERE username='account-reader'");
    ensure(ctype_digit($id), 'Account fixture creation failed');
    $reader = new Browser($owner->base);
    $reader->login('account-reader', 'account-password');
    $before = $sql('SELECT CONCAT(password, encryptionKey) FROM journal.users WHERE id=' . $id);

    $post($reader, '/account/update/email', ['email' => 'Changed@Example.Test']);
    ensure($sql('SELECT emailAddress FROM journal.users WHERE id=' . $id) === 'changed@example.test', 'Email update did not persist normalized address');
    $post($reader, '/account/update/email', ['email' => 'invalid']);
    ensure($sql('SELECT emailAddress FROM journal.users WHERE id=' . $id) === 'changed@example.test', 'Invalid email changed the account');
    $post($reader, '/account/set-date-time-zone', ['timezone' => 'Europe/Amsterdam']);
    ensure($sql('SELECT timezone FROM journal.users WHERE id=' . $id) === 'Europe/Amsterdam', 'Timezone update did not persist');
    ensure(preg_match('/value="Europe\/Amsterdam"\s+selected/', $reader->page('/account')) === 1, 'Updated timezone was not reflected in account settings');
    $post($reader, '/account/set-date-time-zone', ['timezone' => 'not/a-timezone']);
    ensure($sql('SELECT timezone FROM journal.users WHERE id=' . $id) === 'Europe/Amsterdam', 'Invalid timezone changed the account');
    $post($reader, '/account/widgets/update', ['quickAddEntriesBoxEntriesOverview' => 'on']);
    ensure($sql('SELECT enabled FROM journal.widgets WHERE userId=' . $id . " AND name='quickAddEntriesBoxEntriesOverview'") === '1', 'Widget could not be enabled');
    $post($reader, '/account/widgets/update', []);
    ensure($sql('SELECT enabled FROM journal.widgets WHERE userId=' . $id . " AND name='quickAddEntriesBoxEntriesOverview'") === '0', 'Widget could not be disabled');
    $post($reader, '/account/widgets/update', ['unexpected' => 'on']);
    ensure($sql('SELECT COUNT(*) FROM journal.widgets WHERE userId=' . $id . " AND name='unexpected'") === '0', 'Unexpected widget field was accepted');

    foreach ([['wrong-password', 'new-password', 'new-password'], ['account-password', 'new-password', 'different-password'], ['account-password', ['malformed'], ['malformed']]] as [$current, $new, $confirm]) {
        $post($reader, '/account/change-password', ['currentPassword' => $current, 'newPassword' => $new, 'confirmPassword' => $confirm]);
        ensure($sql('SELECT CONCAT(password, encryptionKey) FROM journal.users WHERE id=' . $id) === $before, 'Invalid password form changed credentials');
    }

    $category = $sql('SELECT MIN(id) FROM journal.categories WHERE userId=' . $id);
    $reader->request('/entry/create/action', ['form_key' => Browser::token($reader->page('/entry/create')), 'category_id' => $category, 'entry_title' => 'Account export title', 'entry_content' => '<p>Account export plaintext</p>']);
    $post($reader, '/account/export-entries', []);
    $fileName = waitFor(static function () use ($reader) : false|string {
        preg_match('/name="fileName" value="([^"]+\.zip)"/', $reader->page('/account'), $match);

        return isset($match[1]) ? html_entity_decode($match[1], ENT_QUOTES) : false;
    }, 'account export publication', 30);
    $download = '/account/export-entries/download/' . rawurlencode($fileName);
    [$status, $archive] = $reader->request($download);
    ensure($status === 200 && str_starts_with($archive, 'PK'), 'Account export download failed');
    ensure(str_contains($reader->headers['cache-control'] ?? '', 'no-store'), 'Export download was cacheable');
    ensure($owner->request($download)[0] === 404, 'Another account downloaded a private export');
    file_put_contents($temporary . '/account-export.zip', $archive);
    $zip = new ZipArchive();
    ensure($zip->open($temporary . '/account-export.zip') === true, 'Export archive could not be opened');
    ensure($zip->numFiles === 1 && str_contains($zip->getFromIndex(0), 'Account export plaintext'), 'Account export lost entry content');
    $zip->close();
    $post($reader, '/account/export-entries/delete', ['fileName' => $fileName]);
    ensure( ! str_contains($reader->page('/account'), $fileName), 'Deleted export still listed');
    ensure($reader->request($download)[0] === 404, 'Deleted export still downloadable');

    $post($reader, '/account/change-password', ['currentPassword' => 'account-password', 'newPassword' => 'changed-password', 'confirmPassword' => 'changed-password']);
    ensure(str_contains($reader->request('/entries')[2], '/login'), 'Password change did not revoke the old session');
    $reader->login('account-reader', 'changed-password', '/entries');
    $entryId = $sql('SELECT id FROM journal.entries WHERE userId=' . $id);
    ensure(str_contains($reader->page('/entry/' . $entryId), 'Account export plaintext'), 'Password update changed the content key');
    $post($reader, '/account/delete', ['password' => 'wrong-password']);
    ensure($sql('SELECT COUNT(*) FROM journal.users WHERE id=' . $id) === '1', 'Wrong password deleted an account');
    $post($reader, '/account/delete', ['password' => 'changed-password']);
    ensure($sql('SELECT COUNT(*) FROM journal.users WHERE id=' . $id) === '0', 'Self-service deletion failed');
    ensure($sql('SELECT COUNT(*) FROM journal.entries WHERE userId=' . $id) === '0', 'Self-service deletion retained entries');
    ensure(str_contains($reader->request('/entries')[2], '/login'), 'Deleted account remained authenticated');
    $post($owner, '/account/delete', ['password' => 'fresh-password']);
    ensure($sql("SELECT COUNT(*) FROM journal.users WHERE username='freshowner'") === '1', 'Owner self-deletion was allowed');
    echo "Account HTTP: email/timezone/widgets, invalid forms, export ownership/download/removal, password/key continuity and self-deletion passed\n";
}
