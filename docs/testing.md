# Testing Guide

This document describes how to write and run tests for the Journal application.

## Overview

The test suite uses:
- **PHPUnit 13** for the testing framework
- **Unit tests** for archive storage and filename ownership without a database
- **Transaction-based isolation** - each test runs in a transaction that is rolled back
- **Factory pattern** for creating test entities
- **TestOrm** for raw SQL assertions

## Test Structure

```
tests/
├── bootstrap.php                    # Test bootstrap
├── TestFramework/                   # Shared test infrastructure
│   ├── IntegrationTestCase.php     # Base class for integration tests
│   ├── TestContext.php             # Global context for factories
│   ├── Factory/                    # Entity factories
│   │   ├── UserFactory.php
│   │   ├── CategoryFactory.php
│   │   ├── EntryFactory.php
│   │   └── TemplateFactory.php
│   └── TestOrm/                    # ORM abstraction
│       ├── TestOrm.php
│       ├── TestOrmTransaction.php
│       └── Doctrine/
│           ├── DoctrineTestOrm.php
│           └── DoctrineTestOrmTransaction.php
└── Integration/                    # Integration tests
    └── Repository/
        ├── UserRepositoryTest.php
        └── CategoryRepositoryTest.php
```

## Setup

The test environment is automatically configured when using the development startup script:

```bash
./bin-dev/start
```

This script:
1. Creates the `journal_test` database if it doesn't exist
2. Runs migrations on the test database
3. Uses the same Docker MariaDB instance as development

The `.env.test` file is tracked in git and pre-configured to work with the Docker setup.

### Manual Setup (without bin-dev/start)

If you need to set up the test database manually:

```bash
# Create test database
mysql -u journal -pjournal -e "CREATE DATABASE IF NOT EXISTS journal_test"

# Run migrations
APP_ENV=test ./vendor/bin/doctrine-migrations migrate
```

## Running Tests

For an isolated database without starting or resetting your development stack:

```bash
docker compose -f ci/compose.tests.yaml up -d --wait
APP_ENV=test vendor/bin/doctrine-migrations migrate --no-interaction
vendor/bin/phpunit
docker compose -f ci/compose.tests.yaml down -v
```

This dedicated Compose project exposes its test database on loopback port 3306.
Stop if that port is already in use; do not stop an unrelated database to run it.
The cache filesystem must enforce OS file locks. When running PHPUnit in Docker
on macOS, put `private/cache` on a native Docker volume or tmpfs rather than the
Mac source bind mount. The account-deletion/export lock regression checks this
behavior; a failing lock check must not be skipped.
To exercise MariaDB, use
`TEST_DATABASE_IMAGE=mariadb:10.11 docker compose -f ci/compose.tests.yaml up -d --wait`
after removing only this disposable test project. Never use the teardown command
against development or production Compose files.

CI runs the suite on patched PHP 8.5 (at least 8.5.12) with MySQL 5.7/8.0 and
MariaDB 10.4/10.11. It also runs
`php ci/verify-install-upgrade.php` to build the image, verify fresh startup,
and rehearse a v1.5.1 upgrade using original-code-generated synthetic data.
The checks workflow does not publish images or deploy applications. The separate
Docker release workflow publishes a version tag and `latest` only for new `v*`
tags, after the same checks pass. Merges to `master` and pull requests run checks
without publishing images. The rehearsal also starts the resolved example
Compose configuration using the local candidate image and an available loopback
port, then checks demo login and container recreation.

Service regression tests cover encryption/password continuity, session lifecycle,
CSRF rotation, ownership checks, content operations, category deletion, media,
account defaults, widgets, and privilege restrictions. Export regressions include
account deletion during a running export and reuse of the deleted username.
Category-search tests
check ownership, result counts, and pagination. Export tests verify all
entries survive matching/sanitized filenames, offline media links work, failures
remove partial archives, and accounts can find/delete only their own archives.
The container rehearsal runs on both database engines and adds HTTP login, existing encrypted data/media, the real
export worker/download (including usernames with spaces and a missing log
directory), failure exit/lock cleanup, persistence, and legacy readback checks.
Its original-v1 data fixture lives in `ci/fixtures/legacy-data.php` and runs only
inside the disposable legacy container. A Chrome test checks TinyMCE image paste,
blob preview, upload, save, and reload, plus login-session rotation and cookie flags.
Chrome and ChromeDriver are test tools, with no browser dependency in the app image.
See [upgrading.md](upgrading.md) for the scope and limits.
Export boundary tests also substitute renderers and job runners, reject jobs when
policy disallows them, and check cleanup if rendering fails. Account setup tests
create two users through the same service and verify separate defaults and keys.

### Run All Tests

```bash
./vendor/bin/phpunit
```

### Run Specific Test File

```bash
./vendor/bin/phpunit tests/Integration/Repository/UserRepositoryTest.php
```

### Run Specific Test Method

```bash
./vendor/bin/phpunit --filter it_should_save_user
```

## Writing Tests

### Basic Test Structure

```php
<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Database\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestFramework\Factory\UserFactory;
use Tests\TestFramework\IntegrationTestCase;

final class UserRepositoryTest extends IntegrationTestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->getRepository(UserRepository::class);
    }

    #[Test]
    public function it_should_find_user_by_username(): void
    {
        // Arrange - Create test data
        UserFactory::setup()
            ->withUsername('testuser')
            ->create();

        // Act - Call the method being tested
        $result = $this->repository->findByUsername('testuser');

        // Assert - Verify the result
        self::assertNotNull($result);
        self::assertSame('testuser', $result->getUsername());
    }
}
```

### Key Points

1. **Extend `IntegrationTestCase`** - Provides kernel booting and transaction management
2. **Call `parent::setUp()`** - Initializes the test environment
3. **Use `#[Test]` attribute** - Marks methods as tests
4. **Use factories for test data** - Ensures clean, reproducible test data

## Factory Usage

### UserFactory

```php
// Basic usage
$user = UserFactory::setup()->create();

// With customizations
$user = UserFactory::setup()
    ->withUsername('admin')
    ->withEmailAddress('admin@example.com')
    ->withPrivilegeLevel(User::PRIVILEGE_LEVEL_ADMIN)
    ->create();

// Get encryption key for content encryption
$userFactory = UserFactory::setup();
$user = $userFactory->create();
$encryptionKey = $userFactory->getDecryptionKey();
```

### CategoryFactory

```php
// Auto-creates user if not provided
$category = CategoryFactory::setup()->create();

// With specific user
$category = CategoryFactory::setup()
    ->withUser($user)
    ->withName('My Category')
    ->withDescription('Category description')
    ->withSortOrder(1)
    ->create();
```

### EntryFactory

```php
// Auto-creates user and category
$entry = EntryFactory::setup()->create();

// With specific content and encryption
$entry = EntryFactory::setup()
    ->withUser($user)
    ->withCategory($category)
    ->withTitle('My Entry')
    ->withContent('Entry content', $encryptionKey)
    ->create();
```

### TemplateFactory

```php
// Same pattern as EntryFactory
$template = TemplateFactory::setup()
    ->withTitle('My Template')
    ->withContent('Template content', $encryptionKey)
    ->create();
```

## TestOrm - Direct SQL Assertions

Use `$this->testOrm` to verify database state directly:

```php
#[Test]
public function it_should_save_user(): void
{
    $user = UserFactory::setup()
        ->withUsername('testuser')
        ->create();

    // Verify directly in database
    $row = $this->testOrm->fetchOneAssoc(
        'SELECT * FROM users WHERE id = ?',
        [$user->getId()]
    );

    self::assertNotNull($row);
    self::assertSame('testuser', $row['username']);
}
```

### Available Methods

```php
// Fetch single row
$row = $this->testOrm->fetchOneAssoc('SELECT * FROM users WHERE id = ?', [1]);

// Fetch all rows
$rows = $this->testOrm->fetchAllAssoc('SELECT * FROM users');

// Save entities directly (usually done via factories)
$this->testOrm->save($entity1, $entity2);
```

## Test Isolation

Each test runs in a database transaction that is automatically rolled back after the test completes. This ensures:

- Tests are independent and don't affect each other
- The database is clean before each test
- Tests can run in any order

## Accessing Services

```php
// Get any service from the DI container
$service = $this->getService(UserService::class);

// Get a repository
$repository = $this->getRepository(UserRepository::class);
```

## Best Practices

1. **One behavior per test** - Use the assertions needed to verify its result and side effects
2. **Descriptive test names** - Use names like `it_should_find_user_by_username`
3. **Use factories** - Don't create entities manually in tests
4. **Verify via SQL** - Use `testOrm->fetchOneAssoc()` to verify database state
5. **Don't share state** - Each test should create its own test data
6. **Keep tests fast** - Avoid unnecessary setup

## Handling Encrypted Content

For entities with encrypted content (Entry, Template):

```php
#[Test]
public function it_should_save_entry_with_encrypted_content(): void
{
    $userFactory = UserFactory::setup();
    $user = $userFactory->create();
    $encryptionKey = $userFactory->getDecryptionKey();

    $entry = EntryFactory::setup()
        ->withUser($user)
        ->withContent('My secret content', $encryptionKey)
        ->create();

    // Content is encrypted in database
    $row = $this->testOrm->fetchOneAssoc(
        'SELECT * FROM entries WHERE id = ?',
        [$entry->getId()]
    );

    // Verify encrypted content is not plaintext
    self::assertNotSame('My secret content', $row['content']);
}
```

## Troubleshooting

### Tests fail with "database not found" or "table not found"

Run the development startup script to create the test database and run migrations:

```bash
./bin-dev/start
```

Or manually:

```bash
mysql -u journal -pjournal -e "CREATE DATABASE IF NOT EXISTS journal_test"
APP_ENV=test ./vendor/bin/doctrine-migrations migrate
```

### Tests are slow

- Ensure you're using a local database (not remote)
- Check for N+1 queries in your code
- Consider using database indexes

## Browser, upgrade and coverage checks

Run the Docker rehearsal described in [Upgrading](upgrading.md#reproduce-the-compatibility-checks)
to exercise fresh installs, original v1 data, rollback, the editor, PWA, entry
dates, template titles, account security and optional authenticator login.
It starts only disposable databases, containers and volumes with synthetic data.
Browser tests use ChromeDriver's native WebDriver API from PHP; no Node or Python
toolchain is needed. The example Compose file is also exercised with the local
candidate image, without publishing it.

CI measures application line coverage from PHPUnit plus actual HTTP/CLI execution
on each tested database, and requires at least 85%. The source filter includes
uncovered application services, controllers, validators, framework code, scripts,
production service configuration and entry points. This is combined coverage,
not a claim that unit tests alone cover 85%. Reaching 100% unit coverage remains
a follow-up.

To reproduce with PCOV installed in host PHP:

```sh
mkdir -p coverage
vendor/bin/phpunit --coverage-php=coverage/phpunit.cov
JOURNAL_COVERAGE_DIR="$PWD/coverage" php ci/verify-install-upgrade.php
php ci/coverage/report.php coverage 85
```

The rehearsal builds a test-only PCOV image and captures line data from the web
and CLI processes. The report writes `coverage/clover.xml`, `coverage/summary.txt`
and `coverage/html/`. Container source hashes must match the checkout; rebuild
and start with an empty capture directory after source changes. Release images
do not contain PCOV or the capture hook. Keep coverage artifacts out of Git.

Security regressions cover session revocation, CSRF and method checks, ownership,
image validation, rich-content cleaning, private error reporting, asynchronous
exports, failed storage, concurrent rate limits, authenticator/recovery replay
and credential changes. The Docker check races separate database connections to
prove exactly one winner for a shared counter or recovery code. Do not substitute
these synthetic checks for a deployment-specific backup and HTTPS rehearsal.
