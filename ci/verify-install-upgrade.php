<?php

declare(strict_types=1);

namespace Journal\Checks;

use CurlHandle;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;
use ZipArchive;

// Uses only synthetic data and resources created by this process.
// Requires the project's PHP extensions, Docker Compose, Git, and tar.
const ROOT = __DIR__ . '/..';
const LEGACY_REF = '66c11f976321577fa5ab5e7935696ed273b8defb'; // v1.5.1
const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a6XkAAAAASUVORK5CYII=';

require_once __DIR__ . '/verify-browser.php';

function ensure(bool $condition, string $message) : void
{
    if ( ! $condition) {
        throw new RuntimeException($message);
    }
}

function execute(array $command, ?string $input = null) : array
{
    // Files prevent a full stdout/stderr pipe from blocking Docker builds.
    $stdout = tmpfile();
    $stderr = tmpfile();
    ensure($stdout !== false && $stderr !== false, 'Could not create command output files');

    try {
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => $stdout, 2 => $stderr], $pipes, ROOT);
        ensure(is_resource($process), 'Could not start command');

        if ($input !== null) {
            fwrite($pipes[0], $input);
        }
        fclose($pipes[0]);
        $code = proc_close($process);
        rewind($stdout);
        rewind($stderr);

        return [$code, stream_get_contents($stdout), stream_get_contents($stderr)];
    } finally {
        fclose($stdout);
        fclose($stderr);
    }
}

function run(array $command, ?string $input = null) : string
{
    [$code, $stdout, $stderr] = execute($command, $input);
    ensure($code === 0, $command[0] . ' failed: ' . substr($stderr, -5000) . "\n" . substr($stdout, -3000));

    return $stdout;
}

function waitFor(callable $check, string $description, int $timeout = 120) : mixed
{
    $deadline = microtime(true) + $timeout;
    $last = '';

    do {
        try {
            $result = $check();

            if ($result) {
                return $result;
            }
        } catch (RuntimeException $exception) {
            $last = $exception->getMessage();
        }
        usleep(1_000_000);
    } while (microtime(true) < $deadline);

    throw new RuntimeException('Timed out waiting for ' . $description . ': ' . $last);
}

function removeTemporaryDirectory(string $directory) : void
{
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $file) {
        $file->isDir() && ! $file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($directory);
}

final class Browser
{
    private CurlHandle $client;

    public function __construct(public readonly string $base)
    {
        $this->client = curl_init();
        curl_setopt_array($this->client, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_PROXY => '',
            CURLOPT_COOKIEFILE => '',
        ]);
    }

    public function request(string $path, ?array $fields = null) : array
    {
        curl_setopt($this->client, CURLOPT_URL, $this->base . $path);

        if ($fields === null) {
            curl_setopt($this->client, CURLOPT_HTTPGET, true);
        } else {
            curl_setopt($this->client, CURLOPT_POSTFIELDS, http_build_query($fields));
        }
        $body = curl_exec($this->client);
        ensure($body !== false, 'HTTP request failed: ' . curl_error($this->client));

        return [curl_getinfo($this->client, CURLINFO_RESPONSE_CODE), $body, curl_getinfo($this->client, CURLINFO_EFFECTIVE_URL)];
    }

    public function page(string $path) : string
    {
        [$status, $body] = $this->request($path);
        ensure($status === 200, 'Unexpected HTTP status for ' . $path . ': ' . $status . ' ' . substr($body, 0, 200));

        return $body;
    }

    public static function token(string $body) : string
    {
        ensure(preg_match('/name="form_key"\s+value="([^"]+)"/', $body, $matches) === 1, 'Missing CSRF token');

        return html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public function login(string $username, string $password) : void
    {
        [$status, $body, $location] = $this->request('/login/action', [
            'username' => $username, 'password' => $password, 'form_key' => self::token($this->page('/login')),
        ]);
        ensure($status === 200 && str_contains($location, '/dashboard'), 'Login failed: ' . substr(strip_tags($body), 0, 1600));
        $cookies = curl_getinfo($this->client, CURLINFO_COOKIELIST);
        ensure(array_any($cookies, static fn ($cookie) => (explode("\t", $cookie)[5] ?? '') === 'EEK'), 'Missing encryption-key cookie');
    }
}

function verifyCompose(string $candidate, string $temporary, string $project) : void
{
    // Resolve the quick-start configuration; only image and host port differ.
    $config = json_decode(run(['docker', 'compose', '-p', $project, '-f', ROOT . '/docker-compose.yml', 'config', '--format', 'json']), flags: JSON_THROW_ON_ERROR);
    $config->services->journal->image = $candidate;
    $config->services->journal->ports = [['target' => 80, 'published' => '0', 'host_ip' => '127.0.0.1', 'protocol' => 'tcp']];
    $path = $temporary . '/compose.json';
    file_put_contents($path, json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    $compose = ['docker', 'compose', '-p', $project, '-f', $path];
    $login = static function () use ($compose) : void {
        $address = trim(run([...$compose, 'port', 'journal', '80']));
        $browser = new Browser('http://' . $address);
        waitFor(static fn () => $browser->request('/login')[0] === 200, 'Compose login');
        $browser->login('demouser', 'demopass');
        ensure(str_contains($browser->page('/entries'), 'Entries'), 'Compose entries page failed');
    };
    echo "Checking the example Compose quick start…\n";

    try {
        run([...$compose, 'up', '-d']);
        $login();
        $users = run([...$compose, 'exec', '-T', '--user', 'www-data', 'journal', 'bin/console', 'user:list']);
        run([...$compose, 'up', '-d', '--force-recreate', 'journal']);
        $login();
        ensure(run([...$compose, 'exec', '-T', '--user', 'www-data', 'journal', 'bin/console', 'user:list']) === $users, 'Compose recreation changed users');
        echo "Example Compose: startup, demo login, and recreation passed\n";
    } catch (Throwable $exception) {
        echo substr(run([...$compose, 'logs', '--tail', '35']), -6000);

        throw $exception;
    } finally {
        run([...$compose, 'down', '--volumes', '--remove-orphans']);
    }
}

function main(bool $skipBuild) : void
{
    $prefix = 'journal-check-' . bin2hex(random_bytes(5));
    $temporary = sys_get_temp_dir() . '/' . $prefix;
    mkdir($temporary, 0o700);
    $network = $prefix . '-network';
    $database = $prefix . '-database';
    $candidate = 'journal-platform-candidate:local';
    $legacy = 'journal-platform-legacy:local';
    $databaseImage = getenv('TEST_DATABASE_IMAGE') ?: 'mysql:8.0';
    ensure(in_array($databaseImage, ['mysql:8.0', 'mariadb:10.11'], true), 'Unsupported test database image');
    $isMariaDb = str_starts_with($databaseImage, 'mariadb:');
    $client = $isMariaDb ? 'mariadb' : 'mysql';
    $dumpClient = $isMariaDb ? 'mariadb-dump' : 'mysqldump';
    $containers = $volumes = [];
    $networkCreated = false;

    try {
        if ( ! $skipBuild) {
            echo "Building candidate image (no publishing)…\n";
            run(['docker', 'build', '-f', 'ci/docker/Dockerfile', '-t', $candidate, '.']);
            $source = $temporary . '/legacy';
            mkdir($source);
            run(['git', 'archive', '--format=tar', '--output=' . $temporary . '/legacy.tar', LEGACY_REF]);
            run(['tar', '-xf', $temporary . '/legacy.tar', '-C', $source]);
            echo "Building pinned v1.5.1 test runtime…\n";
            run(['docker', 'build', '-f', ROOT . '/ci/docker/legacy.Dockerfile', '-t', $legacy, $source]);
        }
        run(['docker', 'network', 'create', $network]);
        $networkCreated = true;
        $sql = static fn (string $statement) => trim(run([
            'docker', 'exec', '-i', '-e', 'MYSQL_PWD=journalroot', $database,
            // TCP waits for the final server, not its temporary init socket.
            $client, '--protocol=TCP', '--host=127.0.0.1', '-uroot', '--batch', '--skip-column-names',
        ], $statement));
        $snapshot = static fn (string $schema, array $tables = ['users', 'categories', 'entries', 'templates', 'widgets']) => run([
            'docker', 'exec', '-e', 'MYSQL_PWD=journalroot', $database,
            $dumpClient, '-uroot', '--skip-comments', '--compact', '--skip-add-locks',
            '--skip-disable-keys', '--order-by-primary', ...($isMariaDb ? [] : ['--set-gtid-purged=OFF']), $schema, ...$tables,
        ]);
        $volume = static function (string $suffix) use ($prefix, &$volumes) : string {
            $name = $prefix . '-' . $suffix;
            run(['docker', 'volume', 'create', $name]);
            $volumes[] = $name;

            return $name;
        };
        $browserFor = static function (string $name) : Browser {
            $address = trim(run(['docker', 'port', $name, '80/tcp']));
            $browser = new Browser('http://' . $address);
            waitFor(static fn () => $browser->request('/login')[0] === 200, 'web login');

            return $browser;
        };
        $web = static function (string $suffix, string $schema, string $uploads) use ($prefix, &$containers, $network, $candidate, $browserFor) : array {
            $name = $prefix . '-' . $suffix;
            $containers[] = $name;
            run(['docker', 'run', '-d', '--name', $name, '--network', $network,
                '-p', '127.0.0.1::80', '-v', $uploads . ':/var/www/html/uploads',
                '-e', 'DB_HOST=database', '-e', 'DB_SCHEMA=' . $schema,
                '-e', 'DB_USERNAME=journal', '-e', 'DB_PASSWORD=journal',
                '-e', 'USERNAME=freshowner', '-e', 'PASSWORD=fresh-password',
                '-e', 'EMAIL_ADDRESS=fresh@example.test', '-e', 'DEBUG_MODE=false', $candidate]);

            return [$name, $browserFor($name)];
        };
        $oldData = static function (string $mode, string $uploads) use ($network, $legacy) : void {
            $result = run(['docker', 'run', '--rm', '--network', $network,
                '-v', $uploads . ':/var/www/html/uploads', '-v', ROOT . '/ci/fixtures/legacy-data.php:/checks/legacy-data.php:ro',
                $legacy, 'php', '/checks/legacy-data.php', $mode]);
            $expected = $mode === 'seed' ? 'Legacy fixture seeded' : 'Original v1.5.1 runtime still reads';
            ensure(str_contains($result, $expected), 'Legacy runtime failed: ' . substr($result, -1500));
            echo $expected . ": passed\n";
        };
        $containers[] = $database;
        run(['docker', 'run', '-d', '--name', $database, '--network', $network, '--network-alias', 'database',
            '-e', 'MYSQL_ROOT_PASSWORD=journalroot', '-e', 'MYSQL_DATABASE=journal',
            '-e', 'MYSQL_USER=journal', '-e', 'MYSQL_PASSWORD=journal', $databaseImage]);
        waitFor(static fn () => $sql('SELECT 1') === '1', 'database');
        $sql("CREATE DATABASE journal_upgrade; GRANT ALL ON journal_upgrade.* TO 'journal'@'%';");
        [$fresh, $browser] = $web('fresh', 'journal', $volume('fresh-uploads'));
        // A direct POST has no login form token in its new/expired session.
        $direct = new Browser($browser->base);
        [$status, $body] = $direct->request('/login/action', [
            'username' => 'freshowner', 'password' => 'fresh-password', 'form_key' => 'stale-token',
        ]);
        ensure($status === 200 && str_contains($body, 'Invalid Form Key. Please try again.'), 'Login without a session token should render a validation error, not fail');
        echo "Direct login POST without a session token: validation error rendered\n";
        $browser->login('freshowner', 'fresh-password');
        ensure(str_contains($browser->page('/entries'), 'Entries'), 'Fresh entries page failed');
        verifyEditorBrowser($browser->base, $temporary);
        $freshBefore = $snapshot('journal');
        run(['docker', 'restart', $fresh]);
        $browser = $browserFor($fresh); // Restart can change the ephemeral port.
        $browser->login('freshowner', 'fresh-password');
        ensure($snapshot('journal') === $freshBefore, 'Restart changed existing data');
        ensure($sql('SELECT COUNT(*) FROM journal.users') === '1', 'Restart duplicated users');
        echo "Fresh installation, login, and idempotent restart: passed\n";

        $uploads = $volume('legacy-uploads');
        $oldData('seed', $uploads);
        $before = $snapshot('journal_upgrade');
        $migrationBefore = $snapshot('journal_upgrade', ['migrations']);
        $migrationRows = $sql('SELECT version, executed_at, execution_time FROM journal_upgrade.migrations ORDER BY version');
        ensure($sql('SELECT COUNT(*) FROM journal_upgrade.migrations') === '6', 'Expected all six original v1 migration versions');
        ensure($sql("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='journal_upgrade' AND TABLE_NAME='migrations' AND COLUMN_NAME='version'") === '1024', 'Expected the original v1 migration-column width');
        $contentTables = ['categories', 'entries', 'templates', 'widgets'];
        $originalContent = $snapshot('journal_upgrade', $contentTables);
        [$upgraded, $browser] = $web('upgraded', 'journal_upgrade', $uploads);
        ensure($snapshot('journal_upgrade') === $before, 'Upgrade changed v1 schema or stored data');
        ensure($sql('SELECT version, executed_at, execution_time FROM journal_upgrade.migrations ORDER BY version') === $migrationRows, 'Upgrade changed migration records');
        ensure($sql("SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='journal_upgrade' AND TABLE_NAME='migrations' AND COLUMN_NAME='version'") === '191', 'Unexpected migration-column width');
        ensure(str_replace('varchar(1024)', 'varchar(191)', $migrationBefore) === $snapshot('journal_upgrade', ['migrations']), 'Migration table changed beyond its documented version-column width');
        echo "Migration tracking table: only version width 1024 -> 191 changed; all six records unchanged\n";
        $browser->login('legacy owner', 'legacy-password');
        $entryId = $sql("SELECT id FROM journal_upgrade.entries WHERE title='Legacy HTML entry'");
        $markdownId = $sql("SELECT id FROM journal_upgrade.entries WHERE title='Legacy Markdown entry'");
        $templateId = $sql("SELECT id FROM journal_upgrade.templates WHERE title='Legacy template'");
        $categoryId = $sql('SELECT categoryId FROM journal_upgrade.entries WHERE id=' . $entryId);
        ensure(str_contains($browser->page('/entry/' . $entryId), 'Legacy private content – café'), 'Legacy HTML failed');
        ensure(str_contains($browser->page('/entry/' . $markdownId), '<strong>Legacy bold</strong>'), 'Legacy Markdown failed');
        ensure(str_contains($browser->page('/template/' . $templateId . '/ajax'), 'Legacy template content'), 'Legacy template failed');
        [$status, $image] = $browser->request('/media/legacy-pixel.png');
        ensure($status === 200 && $image === base64_decode(PNG, true), 'Legacy media failed');
        run(['docker', 'exec', $upgraded, 'rmdir', '/var/www/html/private/cache/export/log']);
        [$status] = $browser->request('/account/export-entries', ['form_key' => Browser::token($browser->page('/account'))]);
        ensure($status === 200, 'Export request failed');
        $exported = waitFor(static fn () => trim(run([
            'docker', 'exec', $upgraded, 'find', '/var/www/html/private/cache/export', '-maxdepth', '1', '-name', '*.zip',
        ])), 'legacy content export', 45);
        $zipPath = $temporary . '/export.zip';
        run(['docker', 'cp', $upgraded . ':' . $exported, $zipPath]);
        $archive = new ZipArchive();
        ensure($archive->open($zipPath) === true, 'Could not open exported ZIP');
        $documents = [];
        $mediaPresent = false;

        for ($index = 0; $index < $archive->numFiles; ++$index) {
            $name = $archive->getNameIndex($index);

            if (str_ends_with($name, '.html')) {
                $documents[] = $archive->getFromIndex($index);
            }
            $mediaPresent = $mediaPresent || str_ends_with($name, 'legacy-pixel.png');
        }
        $archive->close();

        foreach (['Legacy private content – café', 'First collision entry', 'Second collision entry'] as $expected) {
            ensure(array_any($documents, static fn ($document) => str_contains($document, $expected)), 'Export lost content: ' . $expected);
        }
        ensure($mediaPresent, 'Export lost media');
        [$status, $downloaded] = $browser->request('/account/export-entries/download/' . rawurlencode(basename($exported)));
        ensure($status === 200 && $downloaded === file_get_contents($zipPath), 'Export download differs');
        $other = new Browser($browser->base);
        $other->login('legacyother', 'other-password');
        [$status, $denied] = $other->request('/entry/' . $entryId);
        ensure($status === 200 && str_contains($denied, 'was not found') && ! str_contains($denied, 'Legacy private content'), 'Entry ownership failed');
        ensure($other->request('/template/' . $templateId . '/ajax')[0] === 404, 'Template ownership failed');
        ensure( ! str_contains($other->page('/entries?category_id=' . $categoryId), 'Legacy HTML entry'), 'Category search exposed another user');
        [$status, $emptyResult] = $other->request('/account/export-entries', ['form_key' => Browser::token($other->page('/account'))]);
        ensure($status === 200 && str_contains($emptyResult, 'There are no entries to export'), 'Empty account export feedback failed');
        $ownerId = $sql("SELECT id FROM journal_upgrade.users WHERE username='legacy owner'");
        [$code, , $stderr] = execute(['docker', 'exec', $upgraded, 'php',
            '/var/www/html/private/scripts/ExportAllEntriesForUser.php', $ownerId, 'legacy owner', 'invalid-test-key']);
        ensure($code === 1 && str_contains($stderr, 'Export failed:'), 'Failed worker did not report failure');
        ensure(trim(run(['docker', 'exec', $upgraded, 'find', '/var/www/html/private/cache',
            '-maxdepth', '1', '-name', '*export_all_entries_for_user.lock'])) === '', 'Failed worker left its lock');
        ensure($snapshot('journal_upgrade') === $before, 'Read/export checks changed stored data');
        run(['docker', 'rm', '-f', $upgraded]);
        $containers = array_values(array_diff($containers, [$upgraded]));
        [, $browser] = $web('recreated', 'journal_upgrade', $uploads);
        $browser->login('legacy owner', 'legacy-password');
        ensure($browser->request('/media/legacy-pixel.png')[1] === $image, 'Recreation lost media');
        ensure($snapshot('journal_upgrade') === $before, 'Recreation changed stored data');
        $oldData('verify', $uploads);
        [$status] = $browser->request('/account/change-password', [
            'form_key' => Browser::token($browser->page('/account')), 'currentPassword' => 'legacy-password',
            'newPassword' => 'changed-legacy-password', 'confirmPassword' => 'changed-legacy-password',
        ]);
        ensure($status === 200, 'Password change failed');
        $browser->request('/logout');
        $browser->login('legacy owner', 'changed-legacy-password');
        ensure(str_contains($browser->page('/entry/' . $entryId), 'Legacy private content – café'), 'Password change lost entry');
        ensure($browser->request('/media/legacy-pixel.png')[1] === $image, 'Password change lost media');
        ensure($snapshot('journal_upgrade', $contentTables) === $originalContent, 'Password change rewrote content');
        $oldData('verify-changed-password', $uploads);
        $created = $sql('SELECT createdTimestamp FROM journal_upgrade.entries WHERE id=' . $entryId);
        [$status, $body] = $browser->request('/entry/' . $entryId . '/update/action', [
            'form_key' => Browser::token($browser->page('/entry/' . $entryId . '/update')), 'category_id' => $categoryId,
            'entry_title' => 'Legacy HTML entry', 'entry_content' => '<p>Updated legacy entry</p>',
        ]);
        ensure($status === 200 && str_contains($body, 'Updated legacy entry'), 'Could not update legacy entry');
        ensure($sql('SELECT createdTimestamp FROM journal_upgrade.entries WHERE id=' . $entryId) === $created, 'Editing changed creation time');
        [$status, $body] = $browser->request('/entry/create/action', [
            'form_key' => Browser::token($browser->page('/entry/create')), 'category_id' => $categoryId,
            'entry_title' => 'Created after upgrade', 'entry_content' => '<p>New v1 content</p>',
        ]);
        ensure($status === 200 && str_contains($body, 'New v1 content'), 'Could not create new entry after upgrade');
        $oldData('verify-candidate-writes', $uploads);
        echo "v1.5.1 upgrade: unchanged application schema/data, credentials, HTML/Markdown, templates, media,\n";
        echo "ownership, export/download, failure cleanup, recreation, password changes, and old-runtime reading of updated/new entries passed.\n";
        echo 'Legacy schema/data snapshot SHA-256: ' . hash('sha256', $before) . "\n";
        verifyCompose($candidate, $temporary, $prefix . '-compose');
    } catch (Throwable $exception) {
        foreach ($containers as $name) {
            if ($name !== $database) {
                [, $stdout, $stderr] = execute(['docker', 'logs', '--tail', '35', $name]);
                echo 'Diagnostic logs for ' . $name . ":\n" . $stdout . $stderr;
            }
        }

        throw $exception;
    } finally {
        foreach (array_reverse($containers) as $name) {
            execute(['docker', 'rm', '-f', '-v', $name]);
        }

        foreach ($volumes as $name) {
            execute(['docker', 'volume', 'rm', $name]);
        }

        if ($networkCreated) {
            execute(['docker', 'network', 'rm', $network]);
        }
        removeTemporaryDirectory($temporary);
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    main(in_array('--skip-build', $argv, true));
}
