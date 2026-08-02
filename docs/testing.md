# Testing Guide

This document describes how to write and run tests for the Journal application.

## Overview

The test suite uses:
- **PHPUnit 12** for the testing framework
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

1. **One assertion per test** - Each test should verify one specific behavior
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
