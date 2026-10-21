<?php

// Runs ONLY in the isolated v1.5.1 test container, using its original classes.
// All accounts and content below are synthetic.
declare(strict_types=1);

chdir('/var/www/html');
$settings = [
    'SITE_TITLE' => 'Legacy Journal fixture',
    'BASE_URL' => '/',
    'DEBUG_MODE' => 'true',
    'USE_SSL' => 'false',
    'ADMIN_EMAIL_ADDRESS' => 'fixture@example.test',
    'DB_HOST' => 'database',
    'DB_SCHEMA' => 'journal_upgrade',
    'DB_USERNAME' => 'journal',
    'DB_PASSWORD' => 'journal',
];
file_put_contents('.env', implode("\n", array_map(
    static fn ($key, $value) => $key . '=' . json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
    array_keys($settings),
    $settings,
)) . "\n");

$mode = $argv[1] ?? 'seed';

if (in_array($mode, ['seed', 'verify-rollback'], true)) {
    exec('vendor/bin/doctrine-migrations migrate --no-interaction 2>&1', $migrationOutput, $code);

    if ($code !== 0) {
        echo implode("\n", $migrationOutput);
        exit($code);
    }
}
require 'private/init.php';

$users = new App\Service\UserService();
$encryptor = new App\Utility\Encryptor();
$categories = new App\Service\CategoryService();
$templates = new App\Service\TemplateService();
$media = new App\Service\MediaService($encryptor, new App\Service\Helper\MediaHelper());
$userRepository = new App\Database\Repository\UserRepository();
$entryRepository = new App\Database\Repository\EntryRepository();

if ($mode === 'seed') {
    $userId = $users->createUser('legacy owner', 'legacy-password', 'legacy@example.test', 1);
    $otherId = $users->createUser('legacyother', 'other-password', 'other@example.test', 3);
    $user = $users->getUser($userId);
    $key = $encryptor->getKeyFromProtectedKey($user->getEncryptionKey(), 'legacy-password');
    $category = $categories->createCategory($userId, 'Legacy category', 'Kept across upgrades', 7);
    $entries = new App\Service\EntryService();
    $entryId = $entries->createEntry($userId, $key, $category->getId(), 'Legacy HTML entry', '<p>Legacy private content – café</p>');
    $entries->createEntry($userId, $key, $category->getId(), 'Legacy Markdown entry', '**Legacy bold**');
    $entries->createEntry($userId, $key, $category->getId(), 'Same title!', '<p>First collision entry</p>');
    $entries->createEntry($userId, $key, $category->getId(), 'Same title?', '<p>Second collision entry</p>');
    $entries->createEntry($userId, $key, $category->getId(), 'Legacy Unicode – café 日本語', '<p>Unicode metadata remains readable</p>');
    $templates->createTemplate($userId, $key, $category->getId(), 'Legacy template', '<p>Legacy template content</p>');
    $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a6XkAAAAASUVORK5CYII=';
    $image = new App\Service\ValueObject\Image($png, 'legacy-pixel.png', 'png');
    $temporary = tempnam(sys_get_temp_dir(), 'journal-fixture-');
    $media->encryptImage($userId, $image, $key, $temporary);
    echo "Legacy fixture seeded with original v1.5.1 services.\n";
} else {
    $user = $userRepository->findByUsername('legacy owner');

    if ($user === null) {
        throw new RuntimeException('Legacy user missing after upgrade');
    }
    $password = in_array($mode, ['verify-changed-password', 'verify-candidate-writes', 'verify-rollback'], true) ? 'changed-legacy-password' : 'legacy-password';

    if ( ! password_verify($password, $user->getPassword())) {
        throw new RuntimeException('Original runtime cannot verify the account password');
    }
    $key = $encryptor->getKeyFromProtectedKey($user->getEncryptionKey(), $password);
    $entries = $entryRepository->getAllEntriesForUser($user->getId());
    $contents = [];

    foreach ($entries as $entry) {
        $contents[] = $entry->getContentDecrypted($key);
    }

    $titles = array_map(static fn ($entry) => $entry->getTitle(), $entries);

    if ( ! in_array('Legacy Unicode – café 日本語', $titles, true)) {
        throw new RuntimeException('Original runtime cannot read legacy Unicode metadata');
    }
    $expectedContents = in_array($mode, ['verify-candidate-writes', 'verify-rollback'], true)
        ? ['<p>Updated legacy entry</p>', '<p>New v1 content</p>']
        : ['<p>Legacy private content – café</p>'];

    if (array_diff($expectedContents, $contents) !== []) {
        throw new RuntimeException('Original runtime cannot decrypt upgraded data');
    }
    $image = $media->getDecryptedImage($user->getId(), 'legacy-pixel.png', $key);

    if ($image->getBinary() === '') {
        throw new RuntimeException('Original runtime cannot read uploaded media');
    }
    echo "Original v1.5.1 runtime still reads the database and encrypted media.\n";
}
