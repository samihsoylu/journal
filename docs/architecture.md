# Architecture

This guide describes the current Journal application structure.

## Request flow

```mermaid
flowchart LR
    A[public/index.php] --> B[private/init.php]
    B --> C[Kernel]
    C --> D[PHP-DI container]
    A --> E[Router]
    D --> E
    E --> F[Controller]
    F --> G[Service]
    G --> H[Helper / Repository]
    H --> I[Doctrine EntityManager]
    F --> J[Blade template]
```

1. `private/init.php` loads Composer and dotenv, validates required environment values, defines path/runtime constants, starts the PHP session, installs security headers, optionally initializes Sentry, and creates the Kernel.
2. `App\Framework\Kernel` creates the PHP-DI container through `ContainerFactory`.
3. `ContainerFactory` enables autowiring, loads `config/services/prod/*.php`, then overlays `config/services/{environment}/*.php` for non-production environments.
4. `App\Router` loads FastRoute definitions from `private/lib/Router/*.php`, resolves the matched controller from the container, injects route parameters, and invokes the handler method.
5. Controllers coordinate validation, services, redirects, and Blade rendering. Business and persistence logic belongs below the controller layer.

## Source layout

| Path | Responsibility |
| --- | --- |
| `public/` | Web document root, front controller, and directly served assets. |
| `private/framework/` | Kernel, container construction, environment support, and Doctrine factory glue. |
| `private/lib/Controller/` | HTTP use cases and response coordination. |
| `private/lib/Router/` | Route declarations. |
| `private/lib/Service/` | Application operations and domain workflows. |
| `private/lib/Service/Helper/` | Shared ownership/query operations used by services. |
| `private/lib/Database/Model/` | Doctrine entities. |
| `private/lib/Database/Repository/` | Doctrine persistence and queries. |
| `private/lib/Validator/` | Request validation and CSRF checks. |
| `private/lib/Utility/` | Sessions, encryption, templates, process execution, locks, and other infrastructure helpers. |
| `private/templates/` | Blade views and components. |
| `private/scripts/` | Background/CLI application scripts, including user export. |
| `config/services/` | Explicit environment-specific container definitions. |
| `tests/` | PHPUnit integration framework, factories, and tests. |

Composer maps `App\` to `private/lib/` and the more specific `App\Framework\` namespace to `private/framework/`.

## Dependency injection

Most classes are constructor-autowired. Explicit definitions are reserved for infrastructure that needs runtime values or an interface binding.

Production Doctrine definitions live in `config/services/prod/doctrine.php`:

- `DoctrineOrmFactory` receives database environment values and project paths.
- Doctrine Migrations' `DependencyFactory` is created from the ORM factory.
- `EntityManagerInterface` resolves to the shared EntityManager.

The test environment first receives those production definitions, then `config/services/test/TestOrm.php` binds the test ORM and transaction abstractions. Keep environment-only services in their environment directory rather than branching throughout application code.

Application bindings live in `config/services/prod/services.php`: rendering and
template-cache contracts use the Blade adapter, account-key operations use the
existing encryptor, and export storage and jobs use filesystem/background-process
adapters. Services receive these contracts through their constructors. Construct
library adapters at this boundary or inside their infrastructure wrapper; creating
entities and value objects inside a use case is normal.

## HTTP boundaries

Routes are declared in `private/lib/Router/Web.php`. A handler string such as `Entry@create` maps to `App\Controller\Entry::create()`.

Controllers extend `AbstractController`, which provides:

- authentication guards
- route parameter access
- notification/redirect support
- the shared template renderer
- Sentry user context where configured

Validators throw typed `UserException` instances for user-correctable failures. The router sends those through `ExceptionHandler` so forms can be re-rendered with a flash notification. Unexpected exceptions follow the generic error path and optional Sentry reporting.

## Service boundaries

The recent user-service split is an example of the intended direction:

- `UserService` handles the current user's core account operations.
- `UserManagementService` handles administrator CRUD and destructive user lifecycle operations.
- `UserExportService` checks export eligibility and coordinates archive and job contracts.
- `FilesystemExportArchiveStore` lists, finds, and deletes archives for the supplied
  account ID and username. New archives live in account-ID directories; flat v1
  archives remain readable. Paths come from the account, never a submitted path.
  Deletion removes all matching scoped and flat copies and reports failed removal.
- `BackgroundExportJobRunner` handles worker commands, logs, and running-job checks.
  Its exclusive account guard holds the same OS lock as the worker throughout
  account deletion. A running export blocks deletion before files or rows change,
  preventing late plaintext archives even on databases that reuse deleted IDs.
- `EntryExporter` builds a private ZIP directly from decrypted entries/media, uses
  IDs to preserve colliding names, and publishes the file only after completion.
  It receives its renderer and shares safe filename rules with storage and jobs.

`UserSetupHelper` receives repositories, while each call receives the user and
key to set up. `UserManagementService` creates accounts without constructing the
helper or knowing how its category/template dependencies are wired.

## SOLID in these boundaries

- **Single responsibility:** export policy, worker transport, archive storage,
  and template rendering have separate owners and classes.
- **Open/closed:** a different renderer, archive store, or job runner can be bound
  in configuration without changing the export policy. Tests exercise substitutions.
- **Liskov substitution:** implementations keep the same signatures and outcomes;
  substitution tests cover successful exports, rejected jobs, and failure cleanup.
  Existing repository/model inheritance keeps its public contracts.
- **Interface segregation:** rendering does not require cache management, and
  account-key clients do not depend on content/file encryption methods. Contracts
  expose the operations used at each boundary.
- **Dependency inversion:** application services receive those contracts; the
  composition root selects Blade, filesystem, process, and encryption adapters.

These are design rules for future features, not a claim that tests can prove
every design decision correct. Keep contracts focused on real callers rather than
adding a matching interface for every class.

Other feature services include authentication, entries, categories, templates, widgets, and media. Helpers provide reusable queries and ownership checks. Repositories should remain focused on persistence, not request or authorization policy.

## Persistence

Doctrine entities are in `private/lib/Database/Model/`. They share identifiers and timestamps through `AbstractModel`; Doctrine lifecycle callbacks update timestamps during persistence.

Repositories extend `AbstractRepository` and share the injected `EntityManagerInterface`. The common write pattern is:

1. call `queue()` for entities to persist
2. call `remove()` for deletions
3. call `save()` once to flush the unit of work

Migrations are in `private/lib/Database/Migration/` and configured by `migrations.json`. Migrations use raw SQL, not ORM entities, because current entity mappings may not match the historical schema being migrated.

## Encryption and session invariant

Journal uses `defuse/php-encryption` for entry, template, and uploaded-media content.

1. Account creation generates a random encryption key protected by the user's password.
2. The protected key is stored in `users.encryptionKey`; the password is stored separately as an Argon2id hash.
3. Login unlocks the key. Accounts without an authenticator receive the hardened
   `EEK` cookie immediately. Enrolled accounts first get a five-minute HttpOnly
   pending-key cookie, bound to a digest and verified credential snapshot in the
   PHP session. No authenticated session or `EEK` is issued until factor proof succeeds.
4. The server-side session cache stores identity/session data, not the unlocked encryption key.
5. Requests reconstruct the key transiently to encrypt or decrypt content.
6. A password change re-protects the same underlying key, so existing ciphertext remains valid.

This protects database, backup, and upload data at rest. It does not protect against a fully compromised live server that can inspect an active request. Do not persist the unlocked key on the server without explicitly redesigning and documenting the threat model.

Optional authenticator policy lives in `TwoFactorService`, with OTPHP behind
`Totp`, independent recovery-code generation, and `TwoFactorStore` as the storage
contract. `DoctrineTwoFactorStore` uses DBAL transactions and conditional updates
for the two security tables. Settings changes check both account credentials and
factor revision under a user-row lock; proof consumption checks the verified
revision. Disabled rows retain a fresh revision, so old password-only sessions
cannot become valid again after enable/disable. Session creation uses the proved
credential fingerprint, and subsequent requests check current account state.

`AttemptLimiter` uses Symfony's fixed-window policy with explicit filesystem
locks and strict storage that rejects failed reads/writes. It reserves attempts
before verification, with an account-wide budget for factor guesses. Runtime
cache directories require a private filesystem with working locks; independent
hosts need a shared atomic backend before being supported.

## Templates and frontend

The project uses Illuminate Blade directly through `App\Utility\BladeRenderer` and `App\Utility\Template`, without Laravel. Templates live in `private/templates/`, and compiled templates are cached in `private/cache/templates/`.

The frontend has no Node or bundler pipeline. Materialize, SortableJS, TinyMCE,
application JavaScript and CSS are committed under `public/assets/` and served
directly. `ci/frontend-dependencies.json` records archive integrity and deployed
file hashes. The service worker caches only the public offline notice.

Mutating forms carry the current session's anti-CSRF token. Validators compare
typed strings with `hash_equals()` and normally rotate the token after a valid
check. Upload and sort requests retain it so multiple uploads and the containing
form remain usable. Older regular forms can become stale. Login and pending
factor forms have separate tokens before an authenticated session exists.

## CLI and background work

- `bin/console` is the Symfony Console entry point for user administration commands.
- `private/scripts/ExportAllEntriesForUser.php` creates encrypted-content exports in a background process.
- `UserExportService` delegates worker startup to `ExportJobRunner`; its background
  adapter uses the existing command, process, and lock utilities.
- `cli-config.php` and `migrations.json` configure Doctrine Migrations commands.

## Testing architecture

`Tests\TestFramework\IntegrationTestCase` boots a test Kernel and begins a DBAL transaction before each test. Teardown rolls it back. If a database error closes Doctrine's EntityManager, the next setup discards the cached Kernel and builds a fresh one.

Factories create and persist core entities through `TestContext` and `TestOrm`. Direct SQL assertions are available through `TestOrm`, while production repositories and services come from the same DI container used by the application.

See [testing.md](testing.md) for setup and examples.

## Change checklist

When changing a cross-cutting concern, inspect these paths together:

| Concern | Files to inspect |
| --- | --- |
| Bootstrap/runtime | `private/init.php`, `public/index.php`, `bin/console`, `composer.json`, `ci/docker/Dockerfile` |
| Container | `private/framework/`, `config/services/` |
| Routing/controller | `private/lib/Router.php`, `private/lib/Router/`, `private/lib/Controller/` |
| Database | models, repositories, migration, Doctrine config, relevant factories/tests |
| Authentication/encryption | `AuthenticationService`, `UserService`, `UserSession`, `Encryptor`, validators |
| Exports | `UserExportService`, export script, command/process/lock utilities, export template |
| Tests | `.env.test`, `phpunit.xml.dist`, test DI config, `IntegrationTestCase`, factories |
