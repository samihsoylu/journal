<?php

declare(strict_types=1);

namespace Journal\Checks;

use CURLFile;

function verifySecurityRequests(Browser $owner, callable $sql, string $temporary) : void
{
    $post = static fn (Browser $browser, string $page, string $action, array $fields) : array => $browser->request($action, ['form_key' => Browser::token($browser->page($page)), ...$fields]);
    $post($owner, '/user/create', '/user/create/action', ['username' => 'security-user', 'password' => 'security-user-password', 'email' => 'security@example.test', 'privilegeLevel' => 3]);
    $otherId = $sql("SELECT id FROM journal.users WHERE username='security-user'");
    ensure(ctype_digit($otherId), 'Could not create a test user through the form');
    $other = new Browser($owner->base);
    $other->login('security-user', 'security-user-password');
    ensure($other->request('/users')[0] === 403, 'A regular user reached user administration');
    $post($owner, '/user/' . $otherId, '/user/' . $otherId . '/update', ['privilegeLevel' => 2]);
    ensure(str_contains($other->request('/users')[2], '/login'), 'Privilege change did not revoke the old session');
    $other->login('security-user', 'security-user-password', '/users');
    ensure($other->request('/users')[0] === 200, 'Promoted administrator cannot reach user administration');
    $post($owner, '/user/' . $otherId, '/user/' . $otherId . '/update', ['privilegeLevel' => 3]);
    ensure(str_contains($other->request('/users')[2], '/login'), 'Demoted administrator kept the cached session');
    $other->request('/entries');
    $other->login('security-user', 'security-user-password', '/entries');
    ensure($other->request('/users')[0] === 403, 'Demoted administrator kept administrator access');

    $post($owner, '/category/create', '/category/create/action', ['category_name' => 'Security category', 'category_description' => 'Test category']);
    $categoryId = $sql("SELECT id FROM journal.categories WHERE name='Security category'");
    ensure(ctype_digit($categoryId), 'Category create form failed');
    $post($owner, '/category/' . $categoryId . '/update', '/category/' . $categoryId . '/update/action', ['category_name' => 'Security category', 'category_description' => 'Updated category']);
    ensure($sql('SELECT description FROM journal.categories WHERE id=' . $categoryId) === 'Updated category', 'Category update failed');
    $hostile = '<p>Private safe paragraph</p><img src="/media/missing" onerror="window.journalAttack=1"><script>window.journalAttack=1</script><p data-mce-onclick="window.journalAttack=1">Keep text</p>';
    $post($owner, '/template/create', '/template/create/action', ['category_id' => $categoryId, 'template_title' => 'Security template', 'entry_content' => $hostile]);
    $templateId = $sql("SELECT id FROM journal.templates WHERE title='Security template'");
    ensure(ctype_digit($templateId), 'Template create form failed');
    [$status, $json] = $owner->request('/template/' . $templateId . '/ajax');
    ensure($status === 200 && str_starts_with($owner->headers['content-type'] ?? '', 'application/json'), 'Template response is not JSON');
    ensure(str_contains($owner->headers['cache-control'] ?? '', 'no-store'), 'Template JSON may be cached');
    $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    ensure((string) $data['categoryId'] === $categoryId && str_contains($data['content'], 'Private safe paragraph'), 'Template category/content lost');
    ensure( ! str_contains($data['content'], 'onerror') && ! str_contains($data['content'], '<script') && ! str_contains($data['content'], 'data-mce-'), 'Template JSON contains active content');
    ensure($other->request('/template/' . $templateId . '/ajax')[0] === 404, 'Template ownership was bypassed');

    $post($owner, '/entry/create', '/entry/create/action', ['category_id' => $categoryId, 'entry_title' => 'Security entry', 'entry_content' => $hostile]);
    $entryId = $sql("SELECT id FROM journal.entries WHERE title='Security entry'");
    ensure(ctype_digit($entryId), 'Entry create form failed');
    $ciphertext = $sql('SELECT content FROM journal.entries WHERE id=' . $entryId);
    $view = $owner->page('/entry/' . $entryId);
    ensure(str_contains($view, 'Private safe paragraph') && ! str_contains($view, 'window.journalAttack'), 'Entry view rendered active content');
    ensure($sql('SELECT content FROM journal.entries WHERE id=' . $entryId) === $ciphertext && ! str_contains($ciphertext, 'Private safe paragraph'), 'Rendering changed encrypted stored content');
    ensure(str_contains($owner->headers['cache-control'] ?? '', 'no-store'), 'Private HTML may be cached');

    foreach (['/entry/' . $entryId, '/template/' . $templateId, '/category/' . $categoryId, '/user/' . $otherId] as $path) {
        ensure(in_array($owner->request($path . '/delete')[0], [404, 405], true), 'GET deletion endpoint still exists');
        $owner->request($path . '/delete', ['form_key' => ['malformed']]);
    }
    ensure($sql('SELECT COUNT(*) FROM journal.entries WHERE id=' . $entryId) === '1', 'Malformed deletion removed an entry');
    ensure($sql('SELECT COUNT(*) FROM journal.templates WHERE id=' . $templateId) === '1', 'Malformed deletion removed a template');
    ensure($sql('SELECT COUNT(*) FROM journal.categories WHERE id=' . $categoryId) === '1', 'Malformed deletion removed a category');
    ensure($sql('SELECT COUNT(*) FROM journal.users WHERE id=' . $otherId) === '1', 'Malformed deletion removed a user');
    $post($other, '/entries', '/entry/' . $entryId . '/delete', []);
    ensure($sql('SELECT COUNT(*) FROM journal.entries WHERE id=' . $entryId) === '1', 'A different user removed the entry');

    $post($owner, '/entry/' . $entryId . '/update', '/entry/' . $entryId . '/update/action', ['category_id' => $categoryId, 'entry_title' => 'Security entry', 'entry_content' => '<p>Updated private text</p>']);
    ensure(str_contains($owner->page('/entry/' . $entryId), 'Updated private text'), 'Entry update failed');
    $post($owner, '/template/' . $templateId . '/update', '/template/' . $templateId . '/update/action', ['category_id' => $categoryId, 'template_title' => 'Security template', 'entry_content' => '<p>Updated template text</p>']);
    ensure(str_contains($owner->request('/template/' . $templateId . '/ajax')[1], 'Updated template text'), 'Template update failed');

    ensure($owner->request('/media/upload', ['form_key' => ['malformed']])[0] === 403, 'Malformed upload CSRF did not return403');
    ensure($post($owner, '/entry/create', '/media/upload', [])[0] === 400, 'Empty upload did not return400');
    $path = $temporary . '/upload.png';
    file_put_contents($path, '<svg onload="alert(1)"></svg>');
    ensure($post($owner, '/entry/create', '/media/upload', ['file' => new CURLFile($path, 'image/png', 'image.png')])[0] === 400, 'A renamed SVG was accepted');
    file_put_contents($path, base64_decode(PNG, true));
    [$status, $body] = $post($owner, '/entry/create', '/media/upload', ['file' => new CURLFile($path, 'text/plain', 'image.php')]);
    ensure($status === 200, 'A real image was not recognized from its bytes');
    $location = json_decode($body, true, 512, JSON_THROW_ON_ERROR)['location'];
    [$status, $bytes] = $owner->request($location);
    ensure($status === 200 && $bytes === base64_decode(PNG, true), 'Uploaded image content changed');
    ensure(str_contains($owner->headers['cache-control'] ?? '', 'no-store') && str_starts_with($owner->headers['content-type'] ?? '', 'image/png'), 'Private image headers failed');
    ensure($other->request($location)[0] === 404, 'Image ownership was bypassed');

    $post($owner, '/entry/' . $entryId . '/update', '/entry/' . $entryId . '/delete', []);
    $post($owner, '/template/' . $templateId . '/update', '/template/' . $templateId . '/delete', []);
    $post($owner, '/category/' . $categoryId . '/update', '/category/' . $categoryId . '/delete', []);
    ensure($sql('SELECT COUNT(*) FROM journal.entries WHERE id=' . $entryId) === '0', 'Valid entry deletion failed');
    ensure($sql('SELECT COUNT(*) FROM journal.templates WHERE id=' . $templateId) === '0', 'Valid template deletion failed');
    ensure($sql('SELECT COUNT(*) FROM journal.categories WHERE id=' . $categoryId) === '0', 'Valid category deletion failed');
    $post($owner, '/user/' . $otherId, '/user/' . $otherId . '/delete', []);
    ensure($sql('SELECT COUNT(*) FROM journal.users WHERE id=' . $otherId) === '0', 'Valid user deletion failed');
    ensure(str_contains($other->request('/entries')[2], '/login'), 'Deleted user session still works');

    ensure(in_array($owner->request('/logout')[0], [404, 405], true), 'GET logout is still accepted');
    $owner->request('/logout', ['form_key' => ['malformed']]);
    ensure(str_contains($owner->request('/entries')[2], '/entries'), 'Malformed logout token logged the user out');
    $post($owner, '/account', '/logout', []);
    ensure(str_contains($owner->request('/entries')[2], '/login'), 'Valid logout did not revoke access');
    $owner->login('freshowner', 'fresh-password', '/entries');
    echo "HTTP security and CRUD: ownership, CSRF/methods, safe HTML/JSON, image upload/privacy, deleted sessions, and logout passed\n";
}
